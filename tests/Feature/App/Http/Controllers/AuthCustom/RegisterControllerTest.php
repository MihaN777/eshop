<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Events\SessionRegenerated;
use App\Http\Controllers\AuthCustom\RegisterController;
use App\Listeners\NewUserListener;
use App\Listeners\SessionRegeneratedListener;
use App\Models\User;
use App\Notifications\NewUserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $response = $this->get(action([RegisterController::class, 'register']))
            ->assertOk()
            ->assertSee('Регистрация')
            ->assertViewIs('auth_custom.register');
    }

    public function test_sign_up_success(): void {
        Notification::fake();
        Event::fake();

        $request = [
            'name' => 'Test',
            'email' => 'test@vk.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->assertDatabaseMissing('users', [
            'email' => $request['email'],
        ]);

        $response = $this->post(
            action([RegisterController::class, 'signUp']),
            $request
        );

        $response->assertValid();

        $this->assertDatabaseHas('users', [
            'email' => $request['email'],
        ]);

        $user = User::query()
            ->where('email', $request['email'])
            ->first();

        Event::assertDispatched(Registered::class);
        Event::assertListening(Registered::class, NewUserListener::class);

        // Т.к. оповещения обрабатываются через очереди, то listener вызывается явно для проверки отправки оповещения
        $event = new Registered($user);
        $listener = new NewUserListener();
        $listener->handle($event);

        Notification::assertSentTo($user, NewUserNotification::class);

        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(SessionRegenerated::class);
        Event::assertListening(SessionRegenerated::class, SessionRegeneratedListener::class);

        $response->assertRedirect(route('profile'));
    }
}
