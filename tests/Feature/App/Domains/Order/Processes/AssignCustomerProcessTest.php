<?php

namespace Tests\Feature\App\Domains\Order\Processes;

use App\Actions\DTOs\OrderCustomerDTO;
use App\Domains\Order\Processes\AssignCustomerProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\App\Domains\Order\Processes\Traits\HasPassClosure;
use Tests\Support\Concerns\CreatesOrderData;
use Tests\TestCase;

class AssignCustomerProcessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesOrderData;
    use HasPassClosure;

    /**
     * Процесс создаёт order_customer из DTO и привязывает к заказу.
     */
    public function test_creates_order_customer_from_dto(): void
    {
        $order = $this->makeOrder();

        $dto = new OrderCustomerDTO(
            first_name: 'Иван',
            last_name: 'Петров',
            phone: '79990001122',
            email: 'ivan@example.com',
            city: 'Москва',
            address: 'Ленина 1',
        );

        (new AssignCustomerProcess($dto))->handle($order, $this->pass());

        $this->assertDatabaseHas('order_customers', [
            'order_id' => $order->id,
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => 'ivan@example.com',
        ]);
    }
}
