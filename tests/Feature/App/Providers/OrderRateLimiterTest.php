<?php

namespace Tests\Feature\App\Providers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OrderRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Throttle отрабатывает до контроллера и валидации, поэтому попытка
     * тратит лимит независимо от корректности тела запроса.
     */
    private function attempt(string $ip): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('order.handle'), []);
    }

    /**
     * Оформление заказа списывает остатки, поэтому очередь заказов с одного
     * источника отсекается — иначе склад блокируется до автоотмены.
     * Сообщение о блокировке уходит во flash.
     */
    public function test_burst_of_orders_from_one_ip_is_blocked(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->attempt('10.1.0.1');
        }

        $response = $this->attempt('10.1.0.1');

        $response->assertRedirect();
        $this->assertStringContainsString(
            'Слишком много заказов',
            (string) session('ctm_flash_message')
        );
    }

    /**
     * Лимит привязан к источнику: другой адрес не наследует чужой счётчик
     * и блокировку не получает.
     */
    public function test_limit_is_scoped_to_the_source(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->attempt('10.1.1.1');
        }

        $this->attempt('10.1.1.2');

        $this->assertStringNotContainsString(
            'Слишком много заказов',
            (string) session('ctm_flash_message')
        );
    }
}
