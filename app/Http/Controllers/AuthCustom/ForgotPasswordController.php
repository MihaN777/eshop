<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthCustom\ForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(): View
    {
        return view('auth_custom.forgot-password');
    }

    public function forgotPasswordSend(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::ResetLinkSent) {
            flash()->info('Вам отправлена ссылка по электронной почте для сброса пароля.');
            return back();
        }

        return back()->withErrors(['email' => 'Учетные данные не верны.']);
    }
}
