<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class CartController extends Controller
{
    public function __invoke(): View
    {
        return view('client.cart');
    }
}
