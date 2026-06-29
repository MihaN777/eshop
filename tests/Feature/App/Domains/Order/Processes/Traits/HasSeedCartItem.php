<?php

namespace Tests\Feature\App\Domains\Order\Processes\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

trait HasSeedCartItem
{
    private function seedCartItem(Product $product, int $quantity, string $options = ''): void
    {
        $cart = Cart::query()->firstOrCreate([
            'storage_id' => session()->getId(),
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->getKey(),
            'product_id' => $product->getKey(),
            'string_option_values' => $options,
            'price' => $product->price,
            'quantity' => $quantity,
        ]);
    }
}
