<?php

namespace Tests\Feature\App\Http\Controllers\Client;

use App\Models\DeliveryType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(int $amount = 100, string $status = 'pending'): Order
    {
        return Order::query()->create([
            'delivery_type_id' => DeliveryType::query()->create(['title' => 'D', 'price' => 0, 'with_address' => false])->id,
            'payment_method_id' => PaymentMethod::query()->create(['title' => 'P', 'redirect_to_pay' => true])->id,
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    /**
     * Успешный callback: 200 + JSON от провайдера, платёж переходит в paid.
     */
    public function test_callback_returns_200_on_success(): void
    {
        $order = $this->makeOrder();
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'transaction_id' => 'fake-tx-001',
            'provider' => 'fake',
            'status' => 'pending',
        ]);

        $response = $this->postJson(route('payment.callback', 'fake'));

        $response->assertOk()->assertJson(['status' => 'ok']);
        $this->assertSame('paid', $payment->fresh()->status->value());
    }

    /**
     * Неизвестный провайдер: setProviderByName бросает исключение -> контроллер отдаёт 500.
     */
    public function test_callback_returns_500_on_unknown_provider(): void
    {
        $response = $this->postJson(route('payment.callback', 'unknown'));

        $response->assertStatus(500)->assertJsonStructure(['error']);
    }

    /**
     * Провайдер валиден, но платёж по callback не найден -> update() бросает -> 500.
     */
    public function test_callback_returns_500_when_payment_not_found(): void
    {
        $response = $this->postJson(route('payment.callback', 'fake'));

        $response->assertStatus(500)->assertJsonStructure(['error']);
    }
}
