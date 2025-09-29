<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(): View {
        return view('auth_custom.login');
    }

    public function register(): View {
        return view('auth_custom.register');
    }

    public function forgotPassword(): View {
        return view('auth_custom.forgot-password');
    }
}
