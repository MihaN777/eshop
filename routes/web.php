<?php

use App\Http\Controllers\AuthCustomController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profile', [HomeController::class, 'profile'])->name('profile')->middleware(['auth', 'verified']);

Route::middleware('guest')->group(callback: function () {
    Route::get('/login', [AuthCustomController::class, 'login'])->name('login');
    Route::post('/sign-in', [AuthCustomController::class, 'signIn'])->name('sign.in')->middleware('throttle:auth');
    Route::get('/register', [AuthCustomController::class, 'register'])->name('register');
    Route::post('/sign-up', [AuthCustomController::class, 'signUp'])->name('sign.up')->middleware('throttle:auth');
    Route::get('/forgot-password', [AuthCustomController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/forgot-password-send', [AuthCustomController::class, 'forgotPasswordSend'])->name('password.forgot.send')->middleware('throttle:auth');
    Route::get('/reset-password/{token}', [AuthCustomController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password-send', [AuthCustomController::class, 'resetPasswordSend'])->name('password.reset.send')->middleware('throttle:auth');
});

Route::middleware('auth')->group(callback: function () {
    Route::delete('/logout', [AuthCustomController::class, 'logout'])->name('logout');
    Route::get('/email-notice', [AuthCustomController::class, 'emailNotice'])->name('verification.notice');
    Route::post('/email-send', [AuthCustomController::class, 'emailSend'])->name('verification.send')->middleware('throttle:6,1');
    Route::get('/email-verify/{id}/{hash}', [AuthCustomController::class, 'emailVerify'])->name('verification.verify')->middleware('signed');
});

//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');
//
//Route::middleware('auth')->group(function () {
//    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//});

//require __DIR__ . '/auth.php';
