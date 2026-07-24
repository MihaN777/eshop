<?php

namespace Tests\Feature\App\Domains\Order\Processes;

use App\Domains\Order\Processes\AssignProductsProcess;
use App\Models\Option;
use App\Models\OptionValue;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class AssignProductsProcessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesOrderData;

    /**
     * Процесс создаёт order_items из корзины и проставляет сумму заказа (1500 * 2 = 3000).
     */
    public function test_creates_order_items_from_cart_and_sets_amount(): void
    {
        $product = Product::factory()->create(['price' => 1500, 'quantity' => 10]);
        $this->seedCartItem($product, 2);
        $order = $this->makeOrder();

        (new AssignProductsProcess)->handle($order, $this->pass());

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertEquals(3000, $order->fresh()->amount->value());
    }

    /**
     * Опции позиции переносятся из корзины в заказ
     * (cart_item_option_value -> order_item_option_value).
     */
    public function test_transfers_cart_item_option_values_to_order_item(): void
    {
        $option = Option::factory()->create();
        $optionValues = OptionValue::factory(2)->create(['option_id' => $option->id]);

        $product = Product::factory()->create(['quantity' => 10]);
        $cartItem = $this->seedCartItem($product, 1);
        $cartItem->optionValues()->attach($optionValues->pluck('id')->all());

        $order = $this->makeOrder();

        (new AssignProductsProcess)->handle($order, $this->pass());

        $orderItem = $order->orderItems()->first();

        $this->assertEqualsCanonicalizing(
            $optionValues->pluck('id')->all(),
            $orderItem->optionValues->pluck('id')->all()
        );
    }
}
