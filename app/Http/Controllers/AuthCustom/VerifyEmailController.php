<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function emailNotice(): View
    {
        return view('auth_custom.verify-email');
    }

    public function emailSend(Request $request): RedirectResponse
    {
        $request->user()->sendEmailVerificationNotification();
        flash()->info('На ваш адрес электронной почты была отправлена новая ссылка для подтверждения.');

        return back();
    }

    public function emailVerify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('home');
    }
}
