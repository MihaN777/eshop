<?php

namespace App\Actions\Client\Profile;

use App\Support\Traits\DTOs\Makeable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

final class UpdateDTO
{
    use Makeable;

    public function __construct(
        public readonly string          $name,
        public readonly string          $email,
        public readonly Authenticatable $authUser,
    )
    {
    }

    public static function fromRequestWith(Request $request, Authenticatable $authUser): self
    {
        return new self(
            $request->get('name'),
            $request->get('email'),
            $authUser
        );
    }
}
