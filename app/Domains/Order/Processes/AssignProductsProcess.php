<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class AssignProductsProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        $order->orderItems()->createMany(
            cart()->items()->map(
                function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                    ];
                }
            )->toArray()
        );

        return $next($order);
    }
}
