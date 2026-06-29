<?php

namespace Tests\Feature\App\Domains\Order\Processes;

use App\Domains\Order\Exceptions\OrderProcessException;
use App\Domains\Order\Processes\CheckProductQuantitiesProcess;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\App\Domains\Order\Processes\Traits\HasPassClosure;
use Tests\Feature\App\Domains\Order\Processes\Traits\HasSeedCartItem;
use Tests\TestCase;

class CheckProductQuantitiesProcessTest extends TestCase
{
    use RefreshDatabase;
    use HasPassClosure;
    use HasSeedCartItem;

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
