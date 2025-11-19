<?php

namespace App\Actions;

use App\Mail\UserPasswordMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserRegisterAction
{
    public function __invoke(object $dto): User
    {
        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'email_verified_at' => $dto->setVerifiedEmailMark ? now()->format('Y-m-d H:i:s') : null,
        ]);

        if (!$dto->setVerifiedEmailMark) event(new Registered($user));

        Mail::to($dto->email)->send(new UserPasswordMail($dto->email, $dto->password));

        return $user;
    }
}
