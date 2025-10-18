<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;
use Illuminate\Http\Request;

final class UserUpdateDTO
{
    use Makeable;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        return self::make(...$request->only(['name', 'email']));
    }
}
