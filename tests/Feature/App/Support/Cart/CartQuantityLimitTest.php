<?php

namespace Tests\Feature\App\Support\Cart;

use App\Models\CartItem;
use App\Models\Option;
use App\Models\OptionValue;
use App\Models\Product;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class CartQuantityLimitTest extends TestCase
{
    use CreatesOrderData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cart.max_quantity_per_product', 20);
        $this->bindFixedCartStorage('guest-session');
    }

    /**
     * Ключевой обход: разные наборы опций дают РАЗНЫЕ строки корзины, поэтому
     * ограничение на строку не работает — лимит должен считаться по сумме товара.
     */
    public function test_limit_counts_across_option_variants(): void
    {
        Option::factory()->create();
        $first = OptionValue::factory()->create();
        $second = OptionValue::factory()->create();

        $product = Product::factory()->create(['quantity' => 1000]);

        cart()->add($product, 15, [$first->id]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Больше 20 шт.');

        // Вторая строка того же товара: 15 + 10 = 25 > 20.
        cart()->add($product, 10, [$second->id]);
    }

    /**
     * Разные товары считаются независимо — общий лимит их не связывает.
     */
    public function test_limit_is_per_product(): void
    {
        $first = Product::factory()->create(['quantity' => 1000]);
        $second = Product::factory()->create(['quantity' => 1000]);

        cart()->add($first, 20);
        cart()->add($second, 20);

        $this->assertSame(2, CartItem::query()->count());
    }

    /**
     * Потолок товара переопределяет общий лимит из конфига.
     */
    public function test_product_override_takes_precedence(): void
    {
        $product = Product::factory()->create([
            'quantity' => 1000,
            'max_order_quantity' => 3,
        ]);

        cart()->add($product, 3);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Больше 3 шт.');

        cart()->add($product, 1);
    }

    /**
     * Переопределение может быть и выше общего лимита — для оптовых позиций.
     */
    public function test_product_override_can_raise_the_limit(): void
    {
        $product = Product::factory()->create([
            'quantity' => 1000,
            'max_order_quantity' => 50,
        ]);

        cart()->add($product, 50);

        $this->assertSame(50, cart()->items()->first()->quantity);
    }

    /**
     * quantity() заменяет количество строки cart_item.quantity, поэтому саму строку
     * из суммы лимита исключаем — иначе обновление до лимита ложно отклонялось.
     */
    public function test_updating_line_to_the_limit_is_allowed(): void
    {
        $product = Product::factory()->create(['quantity' => 1000]);

        cart()->add($product, 5);
        $item = CartItem::query()->firstOrFail();

        cart()->quantity($item, 20);

        $this->assertSame(20, $item->fresh()->quantity);
    }

    /**
     * cart_item.quantity исключается из суммы лимита,
     * но с учётом других строк того же товара лимит всё равно действует.
     */
    public function test_updating_line_respects_other_lines_of_same_product(): void
    {
        Option::factory()->create();
        $first = OptionValue::factory()->create();
        $second = OptionValue::factory()->create();

        $product = Product::factory()->create(['quantity' => 1000]);

        cart()->add($product, 5, [$first->id]);
        cart()->add($product, 5, [$second->id]);

        $item = CartItem::query()->firstOrFail();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Больше 20 шт.');

        // 16 + вторая строка 5 = 21 > 20.
        cart()->quantity($item, 16);
    }

    /**
     * Абсурдное количество отсекается валидацией на входе.
     */
    public function test_validation_rejects_quantity_above_limit(): void
    {
        $product = Product::factory()->create(['quantity' => 1000]);

        $this->post(route('cart.add', $product), ['quantity' => 21])
            ->assertInvalid(['quantity']);

        $this->assertDatabaseCount('cart_items', 0);
    }
}
