<?php

namespace App\Actions;

use App\Models\User;

class UserDeleteAction
{
    public function __invoke(User $user): bool
    {
        auth()->logout();

        if (!$user->delete()) return false;

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return true;
    }

}
