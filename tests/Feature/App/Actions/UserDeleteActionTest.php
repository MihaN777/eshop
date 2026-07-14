<?php

namespace Tests\Feature\App\Actions;

use App\Actions\UserDeleteAction;
use App\Events\SessionRegenerated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserDeleteActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // request()->session()
        // Инициализируем сессию для глобального request()
        $session = $this->app['session.store'];
        $session->start();

        // Привязываем сессию к текущему запросу
        $request = $this->app['request'];
        $request->setLaravelSession($session);
    }

    /**
     * Успешное удаление пользователя без выхода из системы.
     */
    public function test_deletes_user_without_logout(): void
    {
        $user = User::factory()->create();

        $result = (new UserDeleteAction())($user, authLogout: false);

        $this->assertTrue($result);
        $this->assertModelMissing($user);
    }

    /**
     * Успешное удаление пользователя с выходом из системы и регенерации сессии.
     */
    public function test_deletes_user_with_logout_and_session_regenerated(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $result = (new UserDeleteAction())($user, authLogout: true);

        $this->assertTrue($result);
        $this->assertModelMissing($user);
        $this->assertGuest();

        Event::assertDispatched(SessionRegenerated::class);
    }
}
