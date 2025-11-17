<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DeliveryType;
use App\Models\PaymentMethod;
use App\Support\Exceptions\ProjectException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function order(): View
    {
        $cartItems = cart()->items();

        if ($cartItems->isEmpty()) throw new ProjectException('Корзина пуста');

        return view('client.order', [
            'cartItems' => $cartItems,
            'payments' => PaymentMethod::query()->get(),
            'deliveries' => DeliveryType::query()->get(),
        ]);
    }

    public function handle(): RedirectResponse
    {
        return redirect()->route('profile');
    }
}
