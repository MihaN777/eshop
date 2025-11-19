<?php

namespace App\Actions;

use App\Actions\DTOs\UserRegisterDTO;
use App\Events\SessionRegenerated;
use App\Mail\UserPasswordMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserRegisterAction
{
    public function __invoke(UserRegisterDTO $dto): User
    {
        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'email_verified_at' => $dto->verifiedEmail ? now()->format('Y-m-d H:i:s') : null,
        ]);

        if (!$dto->verifiedEmail) event(new Registered($user));

        if ($dto->loginUser) {
            $oldId = request()->session()->getId();

            auth()->login($user, $dto->rememberUser);

            $newId = request()->session()->getId();
            event(new SessionRegenerated($oldId, $newId));
        }

        Mail::to($dto->email)->send(new UserPasswordMail($dto->email, $dto->password));

        return $user;
    }
}
