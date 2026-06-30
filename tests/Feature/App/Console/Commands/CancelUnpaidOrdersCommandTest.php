<?php

namespace Tests\Feature\App\Console\Commands;

use App\Models\DeliveryType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelUnpaidOrdersCommandTest extends TestCase
{
    use RefreshDatabase;

    private const string TEST_PROVIDER = 'fake';

    private function makeOrder(string $status, Product $product, int $qty, ?CarbonInterface $createdAt = null): Order
    {
        $order = Order::query()->create([
            'delivery_type_id' => DeliveryType::query()->create(['title' => 'D', 'price' => 0, 'with_address' => false])->id,
            'payment_method_id' => PaymentMethod::query()->create(['title' => 'P', 'redirect_to_pay' => true])->id,
            'amount' => 100,
            'status' => $status,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => $qty,
        ]);

        // Т.к. created_at не в $fillable
        if ($createdAt) {
            Order::query()->whereKey($order->id)->update(['created_at' => $createdAt]);
        }

        return $order->fresh();
    }

    /**
     * Просроченный pending-заказ отменяется, остаток товара возвращается (5 + 3 = 8).
     */
    public function test_cancels_stale_pending_order_and_restores_stock(): void
    {
        $product = Product::factory()->create(['quantity' => 5]);
        $order = $this->makeOrder('pending', $product, 3, now()->subHour());

        $this->artisan('eshop:cancel-unpaid-orders', ['--minutes' => 30])
            ->assertSuccessful();

        $this->assertSame('cancelled', $order->fresh()->status->value());
        $this->assertSame(8, $product->fresh()->quantity);
    }

    /**
     * Свежий pending-заказ (в пределах TTL) не трогаем.
     */
    public function test_keeps_recent_pending_order(): void
    {
        $product = Product::factory()->create(['quantity' => 5]);
        $order = $this->makeOrder('pending', $product, 3);

        $this->artisan('eshop:cancel-unpaid-orders', ['--minutes' => 30])
            ->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status->value());
        $this->assertSame(5, $product->fresh()->quantity);
    }

    /**
     * Заказ с оплаченным платежом не отменяется, даже если просрочен.
     */
    public function test_skips_order_with_paid_payment(): void
    {
        $product = Product::factory()->create(['quantity' => 5]);
        $order = $this->makeOrder('pending', $product, 3, now()->subHour());

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => self::TEST_PROVIDER,
            'status' => 'paid',
        ]);

        $this->artisan('eshop:cancel-unpaid-orders', ['--minutes' => 30])
            ->assertSuccessful();

        $this->assertSame('pending', $order->fresh()->status->value());
        $this->assertSame(5, $product->fresh()->quantity);
    }
}
