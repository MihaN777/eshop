<?php

namespace Tests\Feature\App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\CheckProductQuantitiesProcess;
use App\Domains\Order\Processes\DecreaseProductsQuantitiesProcess;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductQuantitiesProcessTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Заглушку для Pipeline.
     * Возвращает замыкание следующего шага Pipeline.
     */
    private function pass(): Closure
    {
        return fn(Order $order) => $order;
    }

    /**
     * Базовый инвариант движка БД: атомарное условное списание не уводит остаток в минус.
     */
    public function test_atomic_conditional_decrement_never_oversells(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);

        // Заказ A списывает 8
        $a = Product::query()->whereKey($product->id)->where('quantity', '>=', 8)->decrement('quantity', 8);
        // Заказ B хочет 5, но осталось только 2 — условие не выполнится
        $b = Product::query()->whereKey($product->id)->where('quantity', '>=', 5)->decrement('quantity', 5);

        $this->assertSame(1, $a);
        $this->assertSame(0, $b);
        $this->assertSame(2, $product->fresh()->quantity);
    }

    /**
     * При нехватке остатка списание бросает OrderProcessException
     * и не уводит quantity в минус (товара 3, в корзине нужно 5).
     */
    public function test_decrease_process_throws_and_keeps_stock_non_negative_when_insufficient(): void
    {
        $product = Product::factory()->create(['quantity' => 3]);
        $this->seedCartItem($product, 5);

        try {
            (new DecreaseProductsQuantitiesProcess())->handle(new Order(), $this->pass());
            $this->fail('Ожидалось OrderProcessException');
        } catch (OrderProcessException) {
            // ожидаемо
        }

        $this->assertSame(3, $product->fresh()->quantity);
    }

    /**
     * При достаточном остатке списание уменьшает quantity на нужное число
     * (товара 10, в корзине 4 -> остаётся 6).
     */
    public function test_decrease_process_subtracts_stock_on_success(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);
        $this->seedCartItem($product, 4);

        (new DecreaseProductsQuantitiesProcess())->handle(new Order(), $this->pass());

        $this->assertSame(6, $product->fresh()->quantity);
    }

    /**
     * Один товар в нескольких позициях корзины (разные опции) суммируется:
     * 6 + 6 = 12 > остаток 10 — должна быть ошибка.
     */
    public function test_check_process_aggregates_same_product_across_cart_items(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);
        $this->seedCartItem($product, 6, '1');
        $this->seedCartItem($product, 6, '2');

        $this->expectException(OrderProcessException::class);

        (new CheckProductQuantitiesProcess())->handle(new Order(), $this->pass());
    }
}
