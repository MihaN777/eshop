<?php

namespace App\Actions;

use App\Models\User;
use App\Support\Session\SessionRegenerator;

class UserDeleteAction
{
    public function __invoke(User $user): bool
    {
        auth()->logout();

        if (!$user->delete()) return false;

        SessionRegenerator::run();

        return true;
    }

}
