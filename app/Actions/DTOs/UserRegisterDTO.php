<?php

namespace App\Actions\DTOs;

use App\Support\Traits\Makeable;
use Illuminate\Http\Request;

final class UserRegisterDTO
{
    use Makeable;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly bool   $verifiedEmail,
        public readonly bool   $loginUser,
        public readonly bool   $rememberUser,
    )
    {
    }

    public static function fromRequest(Request $request, bool $verifiedEmail = true, bool $loginUser = false, bool $rememberUser = false): self
    {
        $data = $request->only(['name', 'email', 'password']);
        $data['verifiedEmail'] = $verifiedEmail;
        $data['loginUser'] = $loginUser;
        $data['rememberUser'] = $rememberUser;

        return self::make(...$data);
    }
}
