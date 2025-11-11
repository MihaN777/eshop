<?php

namespace App\Support\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\Cart\Contracts\CartIdentityStorageContract;
use App\Support\ValueObjects\Price;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

// TODO добавить events для событий корзины

class CartManager
{
    public function __construct(
        protected CartIdentityStorageContract $identityStorage
    )
    {
    }

    private function storageData(string $id): array
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

        try {
            $cart = Cart::query()
                ->updateOrCreate([
                    'storage_id' => $this->identityStorage->get(),
                ], $this->storageData($this->identityStorage->get()));

            $cartItem = CartItem::query()
                ->where('cart_id', $cart->getKey())
                ->where('product_id', $product->getKey())
                ->where('string_option_values', $this->stringedOptionValues($optionValues))
                ->first();

            if ($cartItem) {
                $cartItem->price = $product->price;
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                $cartItem = CartItem::query()->create([
                    'cart_id' => $cart->getKey(),
                    'product_id' => $product->getKey(),
                    'string_option_values' => $this->stringedOptionValues($optionValues),
                    'price' => $product->price,
                    'quantity' => $quantity,
                ]);
            }

            $cartItem->optionValues()->sync($optionValues);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

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

    public function items(): Collection
    {
        $cart = $this->get();

        if (!$cart) return collect();

        return CartItem::query()
            ->with(['product', 'optionValues.option'])
            ->whereBelongsTo($cart)
            ->get();
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
                return $item->amount->raw();
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
                ->first() ?? false; // False для сохранения в кеш (null не сохряняется)
        });
    }
}