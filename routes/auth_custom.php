<?php

use App\Http\Controllers\AuthCustom\ForgotPasswordController;
use App\Http\Controllers\AuthCustom\LoginController;
use App\Http\Controllers\AuthCustom\RegisterController;
use App\Http\Controllers\AuthCustom\ResetPasswordController;
use App\Http\Controllers\AuthCustom\SocialAuthController;
use App\Http\Controllers\AuthCustom\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(callback: function () {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/sign-in', [LoginController::class, 'signIn'])->name('sign.in')->middleware('throttle:auth');
    Route::get('/register', [RegisterController::class, 'register'])->name('register');
    Route::post('/sign-up', [RegisterController::class, 'signUp'])->name('sign.up')->middleware('throttle:auth');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/forgot-password-send', [ForgotPasswordController::class, 'forgotPasswordSend'])->name('password.forgot.send')->middleware('throttle:auth');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password-send', [ResetPasswordController::class, 'resetPasswordSend'])->name('password.reset.send')->middleware('throttle:auth');
    Route::get('/social-auth/{driver}/redirect', [SocialAuthController::class, 'redirect'])->name('social.auth.redirect');
    Route::get('/social-auth/{driver}/callback', [SocialAuthController::class, 'callback'])->name('social.auth.callback');
});

Route::middleware('auth')->group(callback: function () {
    Route::delete('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/email-verify-notice', [VerifyEmailController::class, 'emailNotice'])->name('verification.notice');
    Route::post('/email-verify-send', [VerifyEmailController::class, 'emailSend'])->name('verification.send')->middleware('throttle:6,1');
    Route::get('/email-verify/{id}/{hash}', [VerifyEmailController::class, 'emailVerify'])->name('verification.verify')->middleware('signed');
});
