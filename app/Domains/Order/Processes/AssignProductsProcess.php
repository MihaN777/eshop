<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;
use App\Support\ValueObjects\Price;

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

        $cartAmount = $cart->amount();
        $order->amount = new Price(
            value: $cartAmount->raw() + $order->deliveryType->price->raw(),
            precision: $cartAmount->precision(),
            currency: $cartAmount->currency(),
        );

        $order->save();

        return $next($order);
    }
}
