<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthCustom\SignInRequest;
use App\Http\Requests\AuthCustom\SignUpRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthCustomController extends Controller
{
    public function login(): View
    {
        return view('auth_custom.login');
    }

    public function signIn(SignInRequest $request): RedirectResponse
    {
        if (!auth()->attempt($request->validated())) {
            return back()
                ->withErrors(['email' => 'Учетные данные не верны.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('home'));
    }

    public function register(): View
    {
        return view('auth_custom.register');
    }

    public function signUp(SignUpRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password'))
        ]);

        event(new Registered($user));
        auth()->login($user);

        return redirect()->route('second');
        // return redirect()->intended(route('home'));
    }

    public function logout(): void
    {
        auth()->logout();
    }

    public function forgotPassword(): View
    {
        return view('auth_custom.forgot-password');
    }

    public function resetPassword(): View
    {
        return view('auth_custom.reset-password');
    }

    public function emailNotice(): View
    {
        return view('auth.verify-email');
    }

    public function emailSend(Request $request): RedirectResponse
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Ссылка для подтверждения отправлена!');
    }

    public function emailVerify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('home');
    }
}
