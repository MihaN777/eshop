<?php

namespace App\Support\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\Cart\Contracts\CartIdentityStorageContract;
use App\Support\ValueObjects\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CartManager
{
    public function __construct(
        protected CartIdentityStorageContract $identityStorage
    )
    {
    }

    private function storageDta(string $id): array
    {
        $data = [
            'storage_id' => $id
        ];

        if (auth()->check()) $data['user_id'] = auth()->id();

        return $data;
    }

    private function cacheKey(): string
    {
        return str('cart_' . $this->identityStorage->get())
            ->slug()
            ->value();
    }

    private function forgetCache(): string
    {
        return Cache::forget($this->cacheKey());
    }

    private function stringedOptionValues(array $optionValues = []): string
    {
        sort($optionValues);

        return implode(';', $optionValues);
    }

    public function add(Product $product, int $quantity = 1, array $optionValues = []): Cart
    {
        DB::beginTransaction();

        $cart = Cart::query()->updateOrCreate([
            'storage_id' => $this->identityStorage->get(),
        ], $this->storageDta($this->identityStorage->get()));

        $cartItem = CartItem::query()->updateOrCreate([
            'product_id' => $product->getKey(),
            'string_option_values' => $this->stringedOptionValues($optionValues),
        ], [
            'price' => $product->price,
            'quantity' => DB::raw("quantity + $quantity"),
            'string_option_values' => $this->stringedOptionValues($optionValues),
        ]);

        $cartItem->optionValues()->sync($optionValues);

        DB::commit();

        $this->forgetCache();

        return $cart;
    }

    public function quantity(CartItem $cartItem, int $quantity = 1): void
    {
        $cartItem->update(['quantity' => $quantity]);
        $this->forgetCache();
    }

    public function delete(CartItem $cartItem): void
    {
        $cartItem->delete();
        $this->forgetCache();
    }

    public function truncate(): void
    {
        $this->get()?->delete();
        $this->forgetCache();
    }

    public function cartItems(): Collection
    {
        return $this->get()?->cartItems ?? collect();
    }

    public function count(): int
    {
        return $this->cartItems()->sum('quantity');
    }

    public function amount(): Price
    {
        return Price::make(
            $this->cartItems()->sum(function ($item) {
                return $item->amount->row();
            })
        );
    }

    public function get(): mixed
    {
        return Cache::remember($this->cacheKey(), now()->addHour(), function () {
            return Cart::query()
                ->with('cartItems')
                ->where('storage_id', $this->identityStorage->get())
                ->when(auth()->check(), fn(Builder $query) => $query->orWhere('user_id', auth()->id()))
                ->first() ?? false;
        });
    }
}