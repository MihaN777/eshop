<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Events\SessionRegenerated;
use App\Http\Controllers\AuthCustom\LoginController;
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

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex(): void
    {
        $response = $this->get(action([LoginController::class, 'login']))
            ->assertOk()
            ->assertSee('Вход в аккаунт')
            ->assertViewIs('auth_custom.login');
    }
}
