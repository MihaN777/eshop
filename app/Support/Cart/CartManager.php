<?php

namespace App\Support\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\Cart\Contracts\CartIdentityStorageContract;
use App\Support\Cart\Exceptions\CartManagerException;
use App\Support\ValueObjects\Price;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class CartManager
{
    public function __construct(
        protected CartIdentityStorageContract $identityStorage
    )
    {
    }

    /**
     * @throws Exception
     */
    public function add(Product $product, int $quantity = 1, array $optionValues = []): Cart
    {
        $this->checkQuantityLimit($product, $quantity);

        DB::beginTransaction();

        try {
            $cart = $this->cartQuery()->first()
                ?? Cart::query()->create($this->ownerAttributes());

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

    /**
     * @throws Exception
     */
    public function quantity(CartItem $cartItem, int $quantity = 1): void
    {
        $this->checkOwner($cartItem);
        // Количество строки заменяется, поэтому её саму из суммы исключаем (CartItem $excluding).
        $this->checkQuantityLimit($cartItem->product, $quantity, $cartItem);
        $cartItem->update(['quantity' => $quantity]);
        $this->forgetCache();
    }

    public function delete(CartItem $cartItem): void
    {
        $this->checkOwner($cartItem);
        $cartItem->delete();
        $this->forgetCache();
    }

    /**
     * @throws Exception
     */
    public function truncate(): void
    {
        $cart = $this->get();

        if ($cart) {
            DB::beginTransaction();

            try {
                foreach ($cart->cartItems as $cartItem) {
                    $cartItem->delete();
                }

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }

        $this->forgetCache();
    }

    public function items(): Collection
    {
        $cart = $this->get();

        if (!$cart) {
            return collect();
        }

        return CartItem::query()
            ->with(['product.previewImage', 'optionValues.option'])
            ->whereBelongsTo($cart)
            ->get();
    }

    public function cartItems(): Collection
    {
        $cart = $this->get();

        if (!$cart) {
            return collect();
        }

        return $cart->cartItems;
    }

    public function count(): int
    {
        return $this->cartItems()->sum('quantity');
    }

    public function amount(): Price
    {
        return Price::sum(
            $this->cartItems()->map(fn($item) => $item->amount)
        );
    }

    public function get(): mixed
    {
        return Cache::remember($this->cacheKey(), now()->addHour(), function () {
            return $this->cartQuery()
                ->with('cartItems')
                ->first() ?? false; // False для сохранения в кеш (null не сохряняется)
        });
    }

    /**
     * Логин/регистрация — гостевая корзина сливается в корзину пользователя.
     * Прочая ротация сессии (в т.ч. логаут) — гостевая корзина переезжает на новый id,
     * корзина пользователя остаётся за аккаунтом и гостю не достаётся.
     */
    public function handleSessionRegenerated(string $oldStorageId, string $newStorageId): void
    {
        auth()->check()
            ? $this->mergeGuestCartIntoUser($oldStorageId)
            : $this->moveGuestCart($oldStorageId, $newStorageId);

        $this->forgetCache();
    }

    private function ownerAttributes(): array
    {
        return auth()->check()
            ? ['user_id' => auth()->id(), 'storage_id' => null]
            : ['storage_id' => $this->identityStorage->get(), 'user_id' => null];
    }

    /**
     * Единое правило владения для записи и чтения:
     * авторизованный — по user_id, гость — по storage_id среди корзин без владельца.
     */
    private function cartQuery(): Builder
    {
        return auth()->check()
            ? Cart::query()->where('user_id', auth()->id())
            : Cart::query()->whereNull('user_id')->where('storage_id', $this->identityStorage->get());
    }

    /**
     * Ключ кеша повторяет правило владения из cartQuery():
     * иначе смена владельца при том же storage_id отдаёт чужой закешированный ответ.
     */
    private function cacheKey(): string
    {
        $owner = auth()->check()
            ? 'user-' . auth()->id()
            : 'guest-' . $this->identityStorage->get();

        return str('cart-' . $owner)
            ->slug()
            ->value();
    }

    private function forgetCache(): bool
    {
        return Cache::forget($this->cacheKey());
    }

    private function stringedOptionValues(array $optionValues = []): string
    {
        sort($optionValues);

        return implode(';', $optionValues);
    }

    private function moveGuestCart(string $oldStorageId, string $newStorageId): void
    {
        Cart::query()
            ->whereNull('user_id')
            ->where('storage_id', $oldStorageId)
            ->update(['storage_id' => $newStorageId]);
    }

    /**
     * @throws Throwable
     */
    private function mergeGuestCartIntoUser(string $guestStorageId): void
    {
        $guestCart = Cart::query()
            ->whereNull('user_id')
            ->where('storage_id', $guestStorageId)
            ->first();

        if (!$guestCart) {
            return;
        }

        DB::transaction(function () use ($guestCart) {
            $userCart = Cart::query()->firstOrCreate(
                ['user_id' => auth()->id()],
                ['storage_id' => null]
            );

            foreach ($guestCart->cartItems as $guestItem) {
                $userItem = CartItem::query()
                    ->where('cart_id', $userCart->getKey())
                    ->where('product_id', $guestItem->product_id)
                    ->where('string_option_values', $guestItem->string_option_values)
                    ->first();

                if ($userItem) {
                    $userItem->quantity += $guestItem->quantity;
                    $userItem->save();

                    $guestItem->optionValues()->detach();
                    $guestItem->delete();
                } else {
                    $guestItem->cart_id = $userCart->getKey();
                    $guestItem->save();
                }
            }

            $guestCart->delete();
        });
    }

    private function checkOwner(CartItem $cartItem): void
    {
        $cart = $this->get();

        if (!$cart || $cartItem->cart_id != $cart->getKey()) {
            abort(404);
        }
    }

    /**
     * Потолок количества одного товара в корзине.
     *
     * Считаем сумму по ВСЕМ строкам этого товара: разные наборы опций дают разные
     * cart_items, поэтому ограничение на одну строку обходится добавлением вариантов.
     *
     * @param CartItem|null $excluding строка, чьё количество заменяется (для quantity())
     *
     * @throws CartManagerException
     */
    private function checkQuantityLimit(Product $product, int $quantity, ?CartItem $excluding = null): void
    {
        $limit = $product->maxOrderQuantity();

        $alreadyInCart = $this->cartItems()
            ->where('product_id', $product->getKey())
            ->when($excluding, fn(Collection $cartItems) => $cartItems->where('id', '!=', $excluding->getKey()))
            ->sum('quantity');

        if ($alreadyInCart + $quantity > $limit) {
            throw CartManagerException::exceededQuantityLimit($limit);
        }
    }
}
