<?php

namespace Tests\Support\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryType;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Support\Cart\CartManager;
use App\Support\Cart\Contracts\CartIdentityStorageContract;
use Closure;

trait CreatesOrderData
{
    protected function makeDeliveryType(int $price = 0): DeliveryType
    {
        return DeliveryType::query()->create([
            'title' => 'Доставка',
            'price' => $price,
            'with_address' => false,
        ]);
    }

    protected function makePaymentMethod(bool $redirectToPay = false): PaymentMethod
    {
        return PaymentMethod::query()->create([
            'title' => 'Оплата',
            'redirect_to_pay' => $redirectToPay,
        ]);
    }

    protected function makeOrder(string $status = 'new', ?PaymentMethod $paymentMethod = null): Order
    {
        return Order::query()->create([
            'delivery_type_id' => $this->makeDeliveryType()->id,
            'payment_method_id' => ($paymentMethod ?? $this->makePaymentMethod())->id,
            'amount' => 0,
            'status' => $status,
        ]);
    }

    protected function seedCartItem(Product $product, int $quantity, ?string $storageId = null, string $options = ''): CartItem
    {
        $cart = Cart::query()->firstOrCreate([
            'storage_id' => $storageId ?? session()->getId(),
        ]);

        return CartItem::query()->create([
            'cart_id' => $cart->getKey(),
            'product_id' => $product->getKey(),
            'string_option_values' => $options,
            'price' => $product->price,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Позиция в корзине, принадлежащей аккаунту (storage_id = null).
     */
    protected function seedUserCartItem(Product $product, int $quantity, User $user, string $options = ''): CartItem
    {
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            ['storage_id' => null]
        );

        return CartItem::query()->create([
            'cart_id' => $cart->getKey(),
            'product_id' => $product->getKey(),
            'string_option_values' => $options,
            'price' => $product->price,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Заглушку для Pipeline.
     * Возвращает замыкание следующего шага Pipeline.
     */
    private function pass(): Closure
    {
        return fn (Order $order) => $order;
    }

    /**
     * Делает идентификатор корзины детерминированным (не зависящим от HTTP-сессии),
     * чтобы засеянная корзина совпадала с той, что видит контроллер в запросе.
     */
    protected function bindFixedCartStorage(string $storageId): void
    {
        $this->app->singleton(CartManager::class, fn () => new CartManager(
            new class($storageId) implements CartIdentityStorageContract
            {
                public function __construct(private string $id) {}

                public function get(): string
                {
                    return $this->id;
                }
            }
        ));
    }
}
