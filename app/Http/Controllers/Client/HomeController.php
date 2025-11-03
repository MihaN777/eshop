<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\ViewModels\Client\HomeViewModel;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('client.home', new HomeViewModel);
    }
}
