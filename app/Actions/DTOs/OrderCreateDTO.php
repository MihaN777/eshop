<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;
use Illuminate\Http\Request;

final class OrderCreateDTO
{
    use Makeable;

    public function __construct(
        public readonly array  $customer,
        public readonly bool   $createAccount,
        public readonly string $password,
        public readonly string $deliveryTypeId,
        public readonly string $paymentMethodId,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        $data = [];
        $data['customer'] = $request->get('customer');
        $data['createAccount'] = $request->boolean('create_account');
        $data['password'] = $request->get('password');
        $data['deliveryTypeId'] = $request->get('delivery_type_id');
        $data['paymentMethodId'] = $request->get('payment_method_id');

        return self::make(...$data);
    }
}
