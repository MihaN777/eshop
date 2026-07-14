<?php

namespace Tests\Feature\App\Actions;

use App\Actions\DTOs\OrderCreateDTO;
use App\Actions\OrderCreateAction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class OrderCreateActionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesOrderData;

    /**
     * Экшен создаёт заказ.
     */
    public function test_creates_order(): void
    {
        $user = User::factory()->create();

        $dto = new OrderCreateDTO(
            user_id: $user->id,
            delivery_type_id: $this->makeDeliveryType()->id,
            payment_method_id: $this->makePaymentMethod()->id
        );

        $order = (new OrderCreateAction())($dto);

        $this->assertInstanceOf(Order::class, $order);

        $this->assertDatabaseHas('orders', [
            'user_id' => $dto->user_id,
            'delivery_type_id' => $dto->delivery_type_id,
            'payment_method_id' => $dto->payment_method_id,
            'status' => 'new'
        ]);
    }
}
