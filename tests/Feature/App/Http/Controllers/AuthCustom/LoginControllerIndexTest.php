<?php

use App\Http\Controllers\AuthCustom\LoginController;

test('AuthCustom: Login Index', function () {
    $response = $this->get(action([LoginController::class, 'login']))
        ->assertOk()
        ->assertSee('Вход в аккаунт')
        ->assertViewIs('auth_custom.login');
});
