<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class CheckProductQuantitiesProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        foreach (cart()->items() as $item) {
            if ($item->product->quantity < $item->quantity)
                throw new OrderProcessException('Не достаточное количество товара по остатку');
        }

        return $next($order);
    }
}
