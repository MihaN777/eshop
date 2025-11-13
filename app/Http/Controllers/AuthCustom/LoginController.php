<?php

namespace App\Http\Controllers\AuthCustom;

use App\Events\SessionRegenerated;
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

        $oldId = request()->session()->getId();

        if (!auth()->attempt($request->validated(), $remember)) {
            return back()
                ->withErrors(['email' => 'Учетные данные не верны.'])
                ->onlyInput('email');
        }

        request()->session()->regenerate();
        $newId = request()->session()->getId();

        event(new SessionRegenerated($oldId, $newId));

        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        $oldId = request()->session()->getId();

        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $newId = request()->session()->getId();

        event(new SessionRegenerated($oldId, $newId));

        return redirect()->route('home');
    }
}
