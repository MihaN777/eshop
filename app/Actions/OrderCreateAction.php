<?php

namespace App\Actions;

use App\Actions\DTOs\OrderCreateDTO;
use App\Actions\DTOs\UserRegisterDTO;
use App\Models\Order;

class OrderCreateAction
{
    public function __invoke(OrderCreateDTO $dto): Order
    {
        $user = null;

        if ($dto->createAccount) {
            $userRegisterAction = new UserRegisterAction;

            $user = $userRegisterAction(new UserRegisterDTO(
                $dto->customer['first_name'],
                $dto->customer['email'],
                $dto->password,
                verifiedEmail: true,
                loginUser: true,
                rememberUser: false
            ));
        }

        return Order::query()->create([
            'user_id' => $user?->id,
            'delivery_type_id' => $dto->deliveryTypeId,
            'payment_method_id' => $dto->paymentMethodId,
        ]);
    }
}
