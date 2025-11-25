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
        public readonly bool   $verified_email,
        public readonly bool   $login_user,
        public readonly bool   $remember_user,
    )
    {
    }

    public static function fromRequest(Request $request, bool $verifiedEmail = true, bool $loginUser = false, bool $rememberUser = false): self
    {
        $data = $request->only(['name', 'email', 'password']);
        $data['verified_email'] = $verifiedEmail;
        $data['login_user'] = $loginUser;
        $data['remember_user'] = $rememberUser;

        return self::make(...$data);
    }
}
