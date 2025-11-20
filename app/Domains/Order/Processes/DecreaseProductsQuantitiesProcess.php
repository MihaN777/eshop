<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class DecreaseProductsQuantitiesProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        foreach (cart()->items() as $item) {
            $item->product()->decrement('quantity', $item->quantity);
        }

        return $next($order);
    }
}
