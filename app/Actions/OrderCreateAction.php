<?php

namespace App\Actions;

use App\Actions\DTOs\OrderCreateDTO;
use App\Actions\DTOs\OrderCustomerDTO;
use App\Actions\DTOs\UserRegisterDTO;
use App\Models\Order;

class OrderCreateAction
{
    public function __invoke(OrderCreateDTO $orderDto, OrderCustomerDTO $customerDto, bool $createAccount): Order
    {
        $user = null;

        if ($createAccount) {
            $userRegisterAction = new UserRegisterAction;

            $user = $userRegisterAction(new UserRegisterDTO(
                $customerDto->first_name,
                $customerDto->email,
                $orderDto->password,
                verifiedEmail: true,
                loginUser: true,
                rememberUser: false
            ));
        }

        return Order::query()->create([
            'user_id' => $user?->id ?? auth()->user()?->id,
            'delivery_type_id' => $orderDto->delivery_type_id,
            'payment_method_id' => $orderDto->payment_method_id,
        ]);
    }
}
