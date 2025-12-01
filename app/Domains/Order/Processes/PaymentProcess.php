<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;
use App\Support\Payment\PaymentData;
use App\Support\Payment\PaymentSystem;
use Throwable;

class PaymentProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        $order->append('payment_url');
        $order->payment_url = null;

        if ($order->paymentMethod->redirect_to_pay) {
            try {
                $paymentUrl = PaymentSystem::create(new PaymentData(
                    order_id: $order->id,
                    payment_id: null,
                    payment_uuid: str()->orderedUuid()->toString(),
                    description: "Заказ №{$order->id}",
                    return_url: route('payment.callback'),
                    amount: $order->amount,
                    meta: $order->orderItems
                ));
                // ->url(); // TODO протестировать процес оплаты
            } catch (Throwable $e) {
                throw new OrderProcessException($e->getMessage());
            }

            $order->payment_url = url('provider_pay'); //$paymentUrl;
        }

        return $next($order);
    }
}
