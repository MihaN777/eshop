<?php

namespace App\Http\Controllers\AuthCustom;

use App\Events\SessionRegenerated;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthCustom\SignUpRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(): View
    {
        return view('auth_custom.register');
    }

    public function signUp(SignUpRequest $request): RedirectResponse
    {
        $remember = $request->boolean('remember');

        $oldId = request()->session()->getId();

        $user = User::query()->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password'))
        ]);

        event(new Registered($user));
        auth()->login($user, $remember);

        $newId = request()->session()->getId();
        event(new SessionRegenerated($oldId, $newId));

        return redirect()->route('profile');
    }
}
