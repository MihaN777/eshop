<?php

namespace Tests\Feature\App\Domains\Order\Processes;

use App\Domains\Order\Processes\ChangeStateToPendingProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Traits\CreatesOrderData;
use Tests\TestCase;

class ChangeStateToPendingProcessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesOrderData;

    /**
     * Процесс переводит заказ из new в pending.
     */
    public function test_transitions_order_from_new_to_pending(): void
    {
        $order = $this->makeOrder('new');

        (new ChangeStateToPendingProcess())->handle($order, $this->pass());

        $this->assertSame('pending', $order->fresh()->status->value());
    }
}
