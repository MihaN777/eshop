<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CartAddRequest;
use App\Http\Requests\Client\CartQuantityRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\Exceptions\ProjectException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CartController extends Controller
{
    public function cart(): View
    {
        return view('client.cart', [
            'cartItems' => cart()->items(),
        ]);
    }

    public function add(Product $product, CartAddRequest $request): RedirectResponse
    {
        try {
            cart()->add(
                $product,
                $request->get('quantity', 1),
                $request->get('options', [])
            );
        } catch (Throwable $e) {
            throw new ProjectException('Не удалось добавить товар в корзину', $e->getMessage());
        }

        flash()->info('Товар добавлен в корзину');

        return redirect()->intended(route('cart'));
    }

    public function quantity(CartItem $cartItem, CartQuantityRequest $request): RedirectResponse
    {
        cart()->quantity($cartItem, (int)$request->get('quantity'));
        flash()->info('Количество товаров изменено');

        return redirect()->intended(route('cart'));
    }

    public function delete(CartItem $cartItem): RedirectResponse
    {
        cart()->delete($cartItem);
        flash()->info('Товар был удален');

        return redirect()->intended(route('cart'));
    }

    public function truncate(): RedirectResponse
    {
        cart()->truncate();
        flash()->info('Корзина очищена');

        return redirect()->intended(route('cart'));
    }
}
