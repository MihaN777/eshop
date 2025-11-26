<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;

final class OrderCreateDTO
{
    use Makeable;

    public function __construct(
        public readonly ?int $user_id,
        public readonly int  $delivery_type_id,
        public readonly int  $payment_method_id,
    )
    {
    }
}
