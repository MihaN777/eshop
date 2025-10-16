<?php

namespace App\Actions\Client\Profile;

use DomainException;
use Illuminate\Contracts\Auth\Authenticatable;

class DeleteAction
{
    public function __invoke(Authenticatable $authUser)
    {
        auth()->logout();
        if(!$authUser->delete()) throw new DomainException('Не удалось удалить пользователя');

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

}
