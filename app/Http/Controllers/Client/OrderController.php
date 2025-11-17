<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function order(): View
    {
        return view('client.order', [
            'cartItems' => cart()->items(),
        ]);
    }

    public function handle(): RedirectResponse
    {
        return redirect()->route('order');
    }
}
