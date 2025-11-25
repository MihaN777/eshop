<?php

namespace App\Http\Controllers\Client;

use App\Actions\DTOs\OrderCreateDTO;
use App\Actions\DTOs\OrderCustomerDTO;
use App\Actions\OrderCreateAction;
use App\Domains\Order\Processes\AssignCustomerProcess;
use App\Domains\Order\Processes\AssignProductsProcess;
use App\Domains\Order\Processes\ChangeStateToPendingProcess;
use App\Domains\Order\Processes\CheckProductQuantitiesProcess;
use App\Domains\Order\Processes\ClearCartProcess;
use App\Domains\Order\Processes\DecreaseProductsQuantitiesProcess;
use App\Domains\Order\Processes\OrderProcess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\OrderHandleRequest;
use App\Models\DeliveryType;
use App\Models\PaymentMethod;
use App\Support\Exceptions\ProjectException;
use App\Support\Payment\PaymentData;
use App\Support\Payment\PaymentSystem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function order(): View
    {
        $cartItems = cart()->items();

        if ($cartItems->isEmpty()) throw new ProjectException('Корзина пуста');

        return view('client.order', [
            'cartItems' => $cartItems,
            'payments' => PaymentMethod::query()->get(),
            'deliveries' => DeliveryType::query()->get(),
        ]);
    }

    public function handle(OrderHandleRequest $request, OrderCreateAction $action): RedirectResponse
    {
        $order = $action(
            OrderCreateDTO::make(...$request->only(['password', 'delivery_type_id', 'payment_method_id'])),
            OrderCustomerDTO::fromArray($request->get('customer')),
            $request->boolean('create_account')
        );

        (new OrderProcess($order))
            ->processes([
                new CheckProductQuantitiesProcess(),
                new AssignCustomerProcess($request->get('customer')),
                new AssignProductsProcess(),
                new ChangeStateToPendingProcess(),
                new DecreaseProductsQuantitiesProcess(),
                new ClearCartProcess(),
            ])
            ->run();

        // TODO реализовать оплату заказа через тестовую экваринг систему

        if ($order->paymentMethod->redirect_to_pay) {
            try {
                $paymentUrl = PaymentSystem::create(new PaymentData(
                    id: str()->uuid()->toString(),
                    description: "Заказ пользователя: {$order->orderCustomer->last_name} {$order->orderCustomer->first_name}",
                    returnUrl: route('payment.callback'),
                    amount: $order->amount,
                    meta: $order->orderItems
                ))->url();
            } catch (Throwable $e) {
                throw new ProjectException('Ошибка формирования оплаты заказа', $e->getMessage());
            }

            // return redirect($paymentUrl);
        }

        return redirect()->route('profile');
    }
}
