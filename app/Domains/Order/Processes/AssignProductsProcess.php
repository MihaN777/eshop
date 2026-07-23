<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class AssignProductsProcess implements OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed
    {
        $cart = cart();

        foreach ($cart->items() as $item) {
            $orderItem = $order->orderItems()->create([
                'product_id' => $item->product_id,
                'price' => $item->price,
                'quantity' => $item->quantity,
            ]);

            $orderItem->optionValues()->attach(
                $item->optionValues->pluck('id')->all()
            );
        }

        $order->load(['orderItems.product']);

        $order->amount = $cart->amount()->plus($order->deliveryType->price);

        $order->save();

        return $next($order);
    }
}
