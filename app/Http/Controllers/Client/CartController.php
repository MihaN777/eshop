<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    public function cart(): View
    {
        return view('client.cart');
    }

    public function add(Product $product): RedirectResponse
    {
        flash()->info('Товар добавлен в корзину');

        return redirect()->intended(route('cart'));
    }

    public function quantity($item): RedirectResponse
    {
        flash()->info('Количество товаров изменено');

        return redirect()->intended(route('cart'));
    }

    public function delete($item): RedirectResponse
    {
        flash()->info('Товар был удален');

        return redirect()->intended(route('cart'));
    }

    public function truncate(): RedirectResponse
    {
        flash()->info('Корзина очищена');

        return redirect()->intended(route('cart'));
    }
}
