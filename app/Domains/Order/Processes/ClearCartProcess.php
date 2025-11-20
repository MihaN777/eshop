<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;
use Throwable;

class ClearCartProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        try {
            cart()->truncate();
        } catch (Throwable $e) {
            throw new OrderProcessException($e->getMessage());
        }

        return $next($order);
    }
}
