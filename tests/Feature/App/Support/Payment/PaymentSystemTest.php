<?php

namespace Tests\Feature\App\Support\Payment;

use App\Models\DeliveryType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Support\Payment\Exceptions\PaymentProcessException;
use App\Support\Payment\PaymentSystem;
use App\Support\ValueObjects\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Tests\Support\Payment\FakePaymentProvider;
use Tests\TestCase;

class PaymentSystemTest extends TestCase
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

    private function makePayment(Order $order, string $status = 'pending'): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->id,
            'transaction_id' => 'fake-tx-001',
            'provider' => 'fake',
            'status' => $status,
        ]);
    }

    /**
     * setProviderByName() резолвит провайдера из config('payment.providers.testing').
     */
    public function test_set_provider_by_name_resolves_from_config(): void
    {
        config()->set('payment.providers.testing.fake', ['class' => FakePaymentProvider::class]);

        PaymentSystem::setProviderByName('fake');

        $this->assertInstanceOf(FakePaymentProvider::class, PaymentSystem::$provider);
    }

    /**
     * create() создаёт платёж по заказу и отдаёт payment_url провайдера.
     */
    public function test_create_persists_payment_for_order(): void
    {
        $order = $this->makeOrder(100);

        PaymentSystem::setProvider(new FakePaymentProvider);

        $provider = PaymentSystem::create($order);

        $this->assertSame('https://fake.pay/redirect', $provider->paymentUrl());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'fake',
            'transaction_id' => 'fake-tx-001',
            'status' => 'pending',
        ]);
    }

    /**
     * Успешный callback: платёж и заказ переходят в paid, история validated = Yes.
     */
    public function test_payment_and_order_transferred_to_paid(): void
    {
        $order = $this->makeOrder(100, 'pending');
        $payment = $this->makePayment($order, 'pending');

        $fake = new FakePaymentProvider;
        $fake->validates = true;
        $fake->isPaid = true;
        $fake->notifyOrderId = $order->id;
        $fake->notifyAmount = Price::make(100);
        PaymentSystem::setProvider($fake);

        PaymentSystem::update();

        $this->assertSame('paid', $payment->fresh()->status->value());
        $this->assertSame('paid', $order->fresh()->status->value());
        $this->assertDatabaseHas('payment_histories', [
            'transaction_id' => 'fake-tx-001',
            'validated' => 'Yes',
        ]);
    }

    /**
     * Идемпотентность вебхука: повторное уведомление по уже оплаченному платежу
     * не бросает исключение (иначе переход paid -> paid дал бы ошибку) и ничего не ломает.
     */
    public function test_attempt_to_pay_for_already_paid_payment(): void
    {
        $order = $this->makeOrder(100, 'paid');
        $payment = $this->makePayment($order, 'paid');

        $fake = new FakePaymentProvider;
        $fake->validates = true;
        $fake->isPaid = true;
        $fake->notifyOrderId = $order->id;
        $fake->notifyAmount = Price::make(100);
        PaymentSystem::setProvider($fake);

        PaymentSystem::update();

        $this->assertSame('paid', $payment->fresh()->status->value());
        $this->assertSame('paid', $order->fresh()->status->value());
    }

    /**
     * Проваленная проверка callback: исключение + запись истории с validated = No.
     */
    public function test_throws_exception_on_failed_validation(): void
    {
        $fake = new FakePaymentProvider;
        $fake->validates = false;
        PaymentSystem::setProvider($fake);

        try {
            PaymentSystem::update();
            $this->fail('Ожидалось PaymentProcessException');
        } catch (PaymentProcessException) {
            // ожидаемо
        }

        $this->assertDatabaseHas('payment_histories', ['validated' => 'No']);
    }

    /**
     * Несовпадение суммы: платёж становится paid, но заказ остаётся pending.
     */
    public function test_discrepancy_between_amount_of_payment_and_order(): void
    {
        $order = $this->makeOrder(100, 'pending');
        $payment = $this->makePayment($order, 'pending');

        $fake = new FakePaymentProvider;
        $fake->validates = true;
        $fake->isPaid = true;
        $fake->notifyOrderId = $order->id;
        $fake->notifyAmount = Price::make(50);
        PaymentSystem::setProvider($fake);

        PaymentSystem::update();

        $this->assertSame('paid', $payment->fresh()->status->value());
        $this->assertSame('pending', $order->fresh()->status->value());
    }

    /**
     * Аномалия гонки "отмена <-> вебхук": заказ уже отменён (автоотмена успела раньше), но пришла
     * оплата. Платёж помечается оплаченным (деньги получены), заказ остаётся
     * отменённым и не откатывается, а факт фиксируется через report() для ручной обработки.
     */
    public function test_paid_webhook_for_cancelled_order_keeps_order_cancelled_and_reports(): void
    {
        Exceptions::fake();

        $order = $this->makeOrder(100, 'cancelled');
        $payment = $this->makePayment($order, 'pending');

        $fake = new FakePaymentProvider;
        $fake->validates = true;
        $fake->isPaid = true;
        $fake->notifyOrderId = $order->id;
        $fake->notifyAmount = Price::make(100);
        PaymentSystem::setProvider($fake);

        PaymentSystem::update();

        $this->assertSame('paid', $payment->fresh()->status->value());
        $this->assertSame('cancelled', $order->fresh()->status->value());

        Exceptions::assertReported(
            fn (\Exception $e) => str_contains($e->getMessage(), "#{$order->id}")
        );
    }
}
