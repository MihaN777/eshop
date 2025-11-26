<?php

namespace App\Http\Controllers\Client;

use App\Actions\DTOs\OrderCreateDTO;
use App\Actions\DTOs\OrderCustomerDTO;
use App\Actions\DTOs\UserRegisterDTO;
use App\Actions\OrderCreateAction;
use App\Actions\UserRegisterAction;
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

    public function handle(OrderHandleRequest $request, OrderCreateAction $orderCreateAction): RedirectResponse
    {
        // Создание пользователя и заказа
        $customerDto = OrderCustomerDTO::fromArray($request->get('customer'));

        $user = null;
        if ($request->boolean('create_account')) {
            $userRegisterAction = new UserRegisterAction;

            $user = $userRegisterAction(new UserRegisterDTO(
                $customerDto->fullName(),
                $customerDto->email,
                $request->get('password'),
                verified_email: true,
                login_user: true,
                remember_user: false
            ));
        }

        $order = $orderCreateAction(new OrderCreateDTO(
            $user?->id ?? auth()->id(),
            $request->get('delivery_type_id'),
            $request->get('payment_method_id'),
        ));

        // Обработка заказа
        (new OrderProcess($order))
            ->processes([
                new CheckProductQuantitiesProcess(),
                new AssignCustomerProcess($customerDto),
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
                    return_url: route('payment.callback'),
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
