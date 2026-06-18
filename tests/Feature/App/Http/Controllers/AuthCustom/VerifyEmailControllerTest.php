<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Http\Controllers\AuthCustom\VerifyEmailController;
use App\Models\User;
use App\Notifications\AuthCustom\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $user = User::factory()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password'),
            ]
        );

        $this->actingAs($user);

        $this->get(action([VerifyEmailController::class, 'emailNotice']))
            ->assertOk()
            ->assertSee('Подтверждение электронной почты')
            ->assertViewIs('auth_custom.verify-email');
    }

    public function test_email_send(): void {
        Notification::fake();

        $user = User::factory()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password'),
            ]
        );

        $this->actingAs($user);

        $response = $this->post(action([VerifyEmailController::class, 'emailSend']));

        Notification::assertSentTo($user, VerifyEmailNotification::class);

        $response->assertRedirect();
    }
}
