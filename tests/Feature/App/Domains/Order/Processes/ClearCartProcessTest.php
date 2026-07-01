<?php

namespace Tests\Feature\App\Domains\Order\Processes;

use App\Domains\Order\Processes\ClearCartProcess;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class ClearCartProcessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesOrderData;

    /**
     * Процесс очищает корзину.
     */
    public function test_empties_the_cart(): void
    {
        $product = Product::factory()->create(['quantity' => 10]);
        $this->seedCartItem($product, 2);
        $order = $this->makeOrder();

        $this->assertCount(1, cart()->items());

        (new ClearCartProcess())->handle($order, $this->pass());

        $this->assertCount(0, cart()->items());
    }
}
