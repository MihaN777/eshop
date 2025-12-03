<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;
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
                PaymentSystem::setProviderByName(request()->get('provider', 'yoo_kassa'));

                $paymentUrl = PaymentSystem::create($order)->paymentUrl();
            } catch (Throwable $e) {
                throw new OrderProcessException($e->getMessage());
            }

            $order->payment_url = $paymentUrl;
        }

        return $next($order);
    }
}
