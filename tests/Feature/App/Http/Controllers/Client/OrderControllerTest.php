<?php

namespace Tests\Feature\App\Http\Controllers\Client;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesOrderData;

    private const string CART = 'test-cart';

    private function customerPayload(): array
    {
        return [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'phone' => '79990001122',
            'email' => 'buyer@gmail.com',
            'city' => 'Москва',
            'address' => 'Ленина 1',
        ];
    }

    /**
     * Страница оформления с непустой корзиной рендерится.
     */
    public function test_order_page_renders_with_cart_items(): void
    {
        $this->bindFixedCartStorage(self::CART);
        $this->makeDeliveryType();
        $this->makePaymentMethod();
        $product = Product::factory()->create(['quantity' => 10]);
        $this->seedCartItem($product, 1, self::CART);

        $this->get(route('order'))
            ->assertOk()
            ->assertSee('Оформление заказа')
            ->assertViewIs('client.order');
    }

    /**
     * handle(): без редиректа на оплату — создаётся заказ, клиент, позиции;
     * остаток списывается; корзина очищается; редирект в каталог.
     */
    public function test_handle_creates_order_and_clears_cart(): void
    {
        $this->bindFixedCartStorage(self::CART);
        $product = Product::factory()->create(['price' => 1000, 'quantity' => 10]);
        $this->seedCartItem($product, 2, self::CART);

        $delivery = $this->makeDeliveryType();
        $payment = $this->makePaymentMethod(redirectToPay: false);

        $response = $this->post(route('order.handle'), [
            'customer' => $this->customerPayload(),
            'delivery_type_id' => $delivery->id,
            'payment_method_id' => $payment->id,
            'create_account' => false,
        ]);

        $response->assertRedirect(route('catalog'));

        $this->assertDatabaseHas('orders', ['status' => 'pending']);
        $this->assertDatabaseHas('order_customers', ['email' => 'buyer@gmail.com']);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertSame(8, $product->fresh()->quantity);
        $this->assertCount(0, cart()->items());
    }

    /**
     * handle(): способ оплаты требует редиректа — заказ создаётся, платёж инициируется,
     * редирект уходит на payment_url провайдера.
     */
    public function test_handle_redirects_to_payment_url_when_method_requires_payment(): void
    {
        $this->bindFixedCartStorage(self::CART);
        $product = Product::factory()->create(['price' => 1000, 'quantity' => 10]);
        $this->seedCartItem($product, 1, self::CART);

        $delivery = $this->makeDeliveryType();
        $payment = $this->makePaymentMethod(redirectToPay: true);

        $response = $this->post(route('order.handle'), [
            'customer' => $this->customerPayload(),
            'delivery_type_id' => $delivery->id,
            'payment_method_id' => $payment->id,
            'create_account' => false,
            'provider' => 'fake',
        ]);

        $response->assertRedirect('https://fake.pay/redirect');
        $this->assertDatabaseHas('payments', [
            'provider' => 'fake',
            'payment_url' => 'https://fake.pay/redirect',
        ]);
    }
}
