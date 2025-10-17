<?php

namespace App\Actions\Client\Profile;

use DomainException;

class UpdateAction
{
    public function __invoke(UpdateDTO $data): void
    {
        $data->authUser->fill([
            'name' => $data->name,
            'email' => $data->email,
        ]);

        if ($data->authUser->isDirty('email')) $data->authUser->email_verified_at = null;
        if (!$data->authUser->save()) throw new DomainException('Не удалось сохранить данные');
    }

}
