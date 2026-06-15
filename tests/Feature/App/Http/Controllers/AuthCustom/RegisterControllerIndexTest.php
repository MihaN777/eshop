<?php

use App\Http\Controllers\AuthCustom\RegisterController;

test('AuthCustom: Register Index', function () {
    $response = $this->get(action([RegisterController::class, 'register']))
        ->assertOk()
        ->assertSee('Регистрация')
        ->assertViewIs('auth_custom.register');
});
