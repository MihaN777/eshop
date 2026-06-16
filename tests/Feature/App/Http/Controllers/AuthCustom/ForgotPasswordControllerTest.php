<?php

namespace Tests\Feature\App\Http\Controllers\AuthCustom;

use App\Http\Controllers\AuthCustom\ForgotPasswordController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex(): void
    {
        $this->get(action([ForgotPasswordController::class, 'forgotPassword']))
            ->assertOk()
            ->assertSee('Забыли пароль')
            ->assertViewIs('auth_custom.forgot-password');
    }
}
