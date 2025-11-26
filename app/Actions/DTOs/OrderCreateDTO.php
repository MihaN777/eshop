<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;
use Illuminate\Http\Request;

final class OrderCreateDTO
{
    use Makeable;

    public function __construct(
        public readonly bool    $create_account,
        public readonly ?string $password,
        public readonly int     $delivery_type_id,
        public readonly int     $payment_method_id,
    )
    {
    }

    public static function fromArray(array $array): self
    {
        return self::make(...[
            'create_account' => isset($array['create_account']) ? (bool)$array['create_account'] : false,
            'password' => $array['password'] ?? null,
            'delivery_type_id' => (int)$array['delivery_type_id'],
            'payment_method_id' => (int)$array['payment_method_id'],
        ]);
    }
}
