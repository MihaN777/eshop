<?php

namespace App\Actions;

use App\Models\User;
use App\Support\Session\SessionRegenerator;

class UserDeleteAction
{
    public function __invoke(User $user, bool $authLogout): bool
    {
        if ($authLogout) auth()->logout();

        if (!$user->delete()) return false;

        if ($authLogout) SessionRegenerator::run();

        return true;
    }

}
