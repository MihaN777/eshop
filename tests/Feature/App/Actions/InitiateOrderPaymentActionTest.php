<?php

namespace Tests\Feature\App\Actions;

use App\Actions\InitiateOrderPaymentAction;
use App\Models\DeliveryType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitiateOrderPaymentActionTest extends TestCase
{
    use RefreshDatabase;

    private const string TEST_PROVIDER = 'yoo_kassa';

    private function makeOrder(bool $redirectToPay): Order
    {
        return Order::query()->create([
            'delivery_type_id' => DeliveryType::query()->create(['title' => 'D', 'price' => 0, 'with_address' => false])->id,
            'payment_method_id' => PaymentMethod::query()->create(['title' => 'P', 'redirect_to_pay' => $redirectToPay])->id,
            'amount' => 100,
            'status' => 'pending',
        ]);
    }

    /**
     * Если способ оплаты не требует редиректа — оплата не инициируется (null).
     */
    public function test_returns_null_when_method_does_not_redirect_to_pay(): void
    {
        $order = $this->makeOrder(redirectToPay: false);

        $this->assertNull((new InitiateOrderPaymentAction())($order, self::TEST_PROVIDER));
    }

    /**
     * Идемпотентность: при наличии живого pending-платежа переиспользуется его URL,
     * а не создаётся второй платёж (иначе экшен вызвал бы TEST_PROVIDER с пустыми ключами и упал).
     */
    public function test_reuses_existing_pending_payment_url(): void
    {
        $order = $this->makeOrder(redirectToPay: true);

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => self::TEST_PROVIDER,
            'payment_url' => 'https://pay.example/abc',
        ]);

        $url = (new InitiateOrderPaymentAction())($order, self::TEST_PROVIDER);

        $this->assertSame('https://pay.example/abc', $url);
    }
}
