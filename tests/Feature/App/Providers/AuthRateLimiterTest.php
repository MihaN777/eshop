<?php

namespace Tests\Feature\App\Providers;

use App\Http\Controllers\AuthCustom\LoginController;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Неудачная попытка входа: пользователя не существует, поэтому запрос
     * всегда доходит до конца и тратит ровно один хит лимитера.
     */
    private function attempt(mixed $email, string $ip): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(action([LoginController::class, 'signIn']), [
                'email' => $email,
                'password' => 'wrong-password',
            ]);
    }

    /**
     * Исчерпание лимита с одного IP по разным почтам - блокировка.
     */
    public function test_ip_limit_blocks_attempts_across_different_emails(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 1; $i <= 10; $i++) {
            $this->attempt("user{$i}@vk.com", '10.0.0.1');
        }

        Event::assertNotDispatched(Lockout::class);

        $this->attempt('user11@vk.com', '10.0.0.1');

        Event::assertDispatched(Lockout::class);
    }

    /**
     * Исчерпание лимита по одной почте с разных IP - блокировка.
     */
    public function test_account_limit_blocks_distributed_attempts_on_one_email(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 1; $i <= 20; $i++) {
            $this->attempt('victim@vk.com', "10.0.1.{$i}");
        }

        Event::assertNotDispatched(Lockout::class);

        $this->attempt('victim@vk.com', '10.0.1.21');

        Event::assertDispatched(Lockout::class);
    }

    /**
     * Регистр и пробелы не должны давать новые лимиты, иначе лимит
     * по аккаунту обходится одним пробелом в поле.
     */
    public function test_account_limit_key_is_normalised(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 1; $i <= 20; $i++) {
            $this->attempt('victim@vk.com', "10.0.2.{$i}");
        }

        $this->attempt('  Victim@VK.com  ', '10.0.2.21');

        Event::assertDispatched(Lockout::class);
    }

    /**
     * Пустая почта не должна схлопывать все такие запросы в одно ограничение.
     */
    public function test_requests_without_email_do_not_share_one_bucket(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 1; $i <= 25; $i++) {
            $this->attempt('', "10.0.3.{$i}");
        }

        Event::assertNotDispatched(Lockout::class);
    }

    /**
     * Лимитер читает сырой ввод до валидации: массив в email не должен
     * ни ронять его, ни создавать общее ограничение.
     */
    public function test_array_email_is_handled(): void
    {
        Event::fake([Lockout::class]);

        for ($i = 1; $i <= 25; $i++) {
            $this->attempt(['a', 'b'], "10.0.4.{$i}");
        }

        Event::assertNotDispatched(Lockout::class);
    }

    /**
     * При превышении пользователь возвращается на форму с внятной ошибкой,
     * а не упирается в голый 429 ответ.
     */
    public function test_lockout_redirects_back_with_error(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->attempt("user{$i}@vk.com", '10.0.5.1');
        }

        $response = $this->attempt('user11@vk.com', '10.0.5.1');

        $response->assertRedirect();
        $this->assertStringContainsString(
            'Слишком много попыток',
            session('errors')->first('email')
        );
    }
}
