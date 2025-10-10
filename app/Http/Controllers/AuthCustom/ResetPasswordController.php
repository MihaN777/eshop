<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthCustom\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function resetPassword(string $token): View
    {
        return view('auth_custom.reset-password', ['token' => $token]);
    }

    public function resetPasswordSend(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            flash()->info('Ваш пароль был обновлен.');
            return redirect()->route('login');
        }

        return back()->withErrors(['email' => 'Не корректные учетные данные.']);
    }
}
