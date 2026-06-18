<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Http\Controllers\AuthCustom\ForgotPasswordController;
use App\Models\User;
use App\Notifications\AuthCustom\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private function testingCredentials(): array
    {
        return [
            'email' => 'test@vk.com',
        ];
    }

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

        $user = User::factory()->create($this->testingCredentials());

        $this->post(action([ForgotPasswordController::class, 'forgotPasswordSend']), $this->testingCredentials())
            ->assertRedirect();

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get(route('password.reset', ['token' => $notification->token]));

            $response->assertOk();

            return true;
        });
    }

    public function test_forgot_password_send_fail(): void
    {
        Notification::fake();

        $this->assertDatabaseMissing('users', $this->testingCredentials());

        $this->post(action([ForgotPasswordController::class, 'forgotPasswordSend']), $this->testingCredentials())
            ->assertInvalid(['email']);

        Notification::assertNothingSent();
    }
}
