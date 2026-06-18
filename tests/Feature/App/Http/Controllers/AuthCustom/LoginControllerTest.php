<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Events\SessionRegenerated;
use App\Http\Controllers\AuthCustom\LoginController;
use App\Listeners\SessionRegeneratedListener;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $response = $this->get(action([LoginController::class, 'login']))
            ->assertOk()
            ->assertSee('Вход в аккаунт')
            ->assertViewIs('auth_custom.login');
    }

    public function test_sign_in(): void
    {
        Event::fake();

        $request = [
            'email' => 'test@vk.com',
            'password' => 'password',
        ];

        $user = User::factory()->create(
            [
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]
        );

        $response = $this->post(
            action([LoginController::class, 'signIn']),
            $request
        );

        $response->assertValid()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(SessionRegenerated::class);
        Event::assertListening(SessionRegenerated::class, SessionRegeneratedListener::class);
    }

    public function test_logout(): void
    {
        Event::fake();

        $user = User::factory()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password'),
            ]
        );

        $this->actingAs($user);

        $response = $this->delete(action([LoginController::class, 'logout']));

        $this->assertGuest();

        Event::assertDispatched(SessionRegenerated::class);
        Event::assertListening(SessionRegenerated::class, SessionRegeneratedListener::class);

        $response->assertRedirect(route('home'));
    }
}
