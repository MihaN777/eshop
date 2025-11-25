<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;

final class OrderCustomerDTO
{
    use Makeable;

    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $phone,
        public readonly string $email,
        public readonly string $city,
        public readonly string $address,
    )
    {
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public static function fromArray(array $array): self
    {
        return self::make(...[
            'first_name' => $array['first_name'] ?? '',
            'last_name' => $array['last_name'] ?? '',
            'phone' => $array['phone'] ?? '',
            'email' => $array['email'] ?? '',
            'city' => $array['city'] ?? '',
            'address' => $array['address'] ?? '',
        ]);
    }
}
