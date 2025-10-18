<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthCustom\SignInRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function login(): View
    {
        return view('auth_custom.login');
    }

    public function signIn(SignInRequest $request): RedirectResponse
    {
        $remember = $request->boolean('remember');

        if (!auth()->attempt($request->validated(), $remember)) {
            return back()
                ->withErrors(['email' => 'Учетные данные не верны.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
