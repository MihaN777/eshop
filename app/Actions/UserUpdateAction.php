<?php

namespace App\Actions;

use App\Models\User;

class UserUpdateAction
{
    public function __invoke(User $user, object $dto): bool|User
    {
        $data = [
            'name' => $dto->name,
            'email' => $dto->email,
        ];

        if ($user->email != $dto->email) $data['email_verified_at'] = null;

        if (!$user->update($data)) return false;

        return $user->refresh();
    }

}
