<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Http\Controllers\AuthCustom\ForgotPasswordController;
use App\Models\User;
use App\Notifications\AuthCustom\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $this->get(action([ForgotPasswordController::class, 'forgotPassword']))
            ->assertOk()
            ->assertSee('Забыли пароль')
            ->assertViewIs('auth_custom.forgot-password');
    }

    public function test_forgot_password_send_success(): void
    {
        Notification::fake();

        $user = User::factory()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password'),
            ]
        );

        $this->post(
            action([ForgotPasswordController::class, 'forgotPasswordSend']),
            ['email' => $user->email]
        )->assertRedirect();

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get(route('password.reset', ['token' => $notification->token]));

            $response->assertOk();

            return true;
        });
    }
}
