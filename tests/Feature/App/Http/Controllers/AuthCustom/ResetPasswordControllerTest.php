<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Http\Controllers\AuthCustom\ResetPasswordController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password'),
            ]
        );

        $this->token = Password::createToken($this->user);
    }

    public function test_index(): void
    {
        $this->get(action([ResetPasswordController::class, 'resetPassword'], ['token' => $this->token]))
            ->assertOk()
            ->assertSee('Востановление пароля')
            ->assertViewIs('auth_custom.reset-password');
    }

    public function test_reset_password_send(): void
    {
        $password = 'password123';
        $passwordConfirmation = 'password123';

        Password::shouldReceive('reset')
            ->once()
            ->withSomeOfArgs([
                'email' => $this->user->email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
                'token' => $this->token,
            ])
            ->andReturn(Password::PASSWORD_RESET);

        $response = $this->post(action([ResetPasswordController::class, 'resetPasswordSend']), [
            'email' => $this->user->email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
            'token' => $this->token,
        ]);

        $response->assertRedirect(route('login'));
    }
}
