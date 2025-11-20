<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Domains\Order\States\PendingOrderState;
use App\Models\Order;

class ChangeStateToPendingProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        $order->status->transitionTo(new PendingOrderState($order));

        return $next($order);
    }
}
