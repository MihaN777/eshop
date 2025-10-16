<?php

namespace App\Actions\Client\Profile;

use DomainException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAction
{
    public function __invoke(FormRequest $request, Authenticatable $authUser)
    {
        $authUser->fill($request->validated());

        if ($authUser->isDirty('email')) $authUser->email_verified_at = null;
        if (!$authUser->save()) throw new DomainException('Не удалось сохранить данные');
    }

}
