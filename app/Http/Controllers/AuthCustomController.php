<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthCustom\SignInRequest;
use App\Http\Requests\AuthCustom\SignUpRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

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
        return redirect()->intended('dashboard');
    }

    public function register(): View
    {
        return view('auth_custom.register');
    }

    public function signUp(SignUpRequest $request)
    {
        // TODO
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
}
