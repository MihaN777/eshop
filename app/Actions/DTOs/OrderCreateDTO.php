<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;
use Illuminate\Http\Request;

final class OrderCreateDTO
{
    use Makeable;

    public function __construct(
        public readonly ?string $password,
        public readonly int     $delivery_type_id,
        public readonly int     $payment_method_id,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return self::make(...[
            'password' => $request->get('password'),
            'delivery_type_id' => (int)$request->get('delivery_type_id'),
            'payment_method_id' => (int)$request->get('payment_method_id'),
        ]);
    }
}
