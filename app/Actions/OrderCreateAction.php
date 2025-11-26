<?php

namespace App\Actions;

use App\Actions\DTOs\OrderCreateDTO;
use App\Models\Order;

class OrderCreateAction
{
    public function __invoke(OrderCreateDTO $orderDto): Order
    {
        return Order::query()->create([
            'user_id' => $orderDto->user_id,
            'delivery_type_id' => $orderDto->delivery_type_id,
            'payment_method_id' => $orderDto->payment_method_id,
        ]);
    }
}
