<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Http\Controllers\AuthCustom\VerifyEmailController;
use App\Models\User;
use App\Notifications\AuthCustom\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $user = User::factory()->unverified()->create(
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

    public function test_email_send(): void
    {
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

    public function test_email_verify_success(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password')
            ]
        );

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)
            ->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

        $response->assertRedirect(route('home'));
    }

    public function test_email_verify_fail(): void
    {
        $user = User::factory()->unverified()->create(
            [
                'email' => 'test@vk.com',
                'password' => Hash::make('password')
            ]
        );

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    }
}
