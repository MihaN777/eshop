<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): View {
        return view('index');
    }

    public function login(Request $request): View {
        return view('auth_custom/index');
    }
}
