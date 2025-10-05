<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(): View {
        return view('index');
    }

    public function profile(): View {
        return view('profile');
    }
}
