<?php

namespace App\Http\Controllers\AuthCustom;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    public function logout(): RedirectResponse
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
