<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class AssignProductsProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        $cart = cart();

        $order->orderItems()->createMany(
            $cart->items()->map(
                function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                    ];
                }
            )->toArray()
        );

        $order->amount = $cart->amount();
        $order->save();

        return $next($order);
    }
}
