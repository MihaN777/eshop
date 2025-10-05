<?php

use App\Http\Controllers\AuthCustomController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profile', [HomeController::class, 'profile'])->name('profile')->middleware(['auth', 'verified']);

Route::middleware('guest')->group(callback: function () {
    Route::get('/login', [AuthCustomController::class, 'login'])->name('login');
    Route::post('/sign_in', [AuthCustomController::class, 'signIn'])->name('sign-in');
    Route::get('/register', [AuthCustomController::class, 'register'])->name('register');
    Route::post('/sign_up', [AuthCustomController::class, 'signUp'])->name('sign-up');
    Route::post('/logout', [AuthCustomController::class, 'logout'])->name('logout');
    Route::get('/forgot_password', [AuthCustomController::class, 'forgotPassword'])->name('forgot-password');
    Route::get('/reset_password', [AuthCustomController::class, 'resetPassword'])->name('reset-password');
});

Route::middleware('auth')->group(callback: function () {
    Route::get('/email_notice', [AuthCustomController::class, 'emailNotice'])->name('verification.notice');
    Route::post('/email_send', [AuthCustomController::class, 'emailSend'])->name('verification.send')->middleware('throttle:6,1');
    Route::get('/email_verify/{id}/{hash}', [AuthCustomController::class, 'emailVerify'])->name('verification.verify')->middleware('signed');
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
