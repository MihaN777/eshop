<?php

use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CatalogController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Middleware\CatalogViewMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Каталог
Route::get('/catalog/{category:slug?}', CatalogController::class)->name('catalog')->middleware([CatalogViewMiddleware::class]);
Route::get('/product/{product:slug}', ProductController::class)->name('product');

// Корзина
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'cart'])->name('cart');
    Route::post('/add/{product:slug}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/quantity/{cart_item}', [CartController::class, 'quantity'])->name('cart.quantity');
    Route::delete('/delete/{cart_item}', [CartController::class, 'delete'])->name('cart.delete');
    Route::delete('/truncate', [CartController::class, 'truncate'])->name('cart.truncate');
});

// Заказ
Route::prefix('order')->group(function () {
    Route::get('/', [OrderController::class, 'order'])->name('order');
    Route::post('/handle', [OrderController::class, 'handle'])->name('order.handle');
});

// Оплата
Route::prefix('payment')->group(function () {
    Route::get('/', [PaymentController::class, 'payment'])->name('payment');
    Route::post('/callback', [PaymentController::class, 'callback'])->name('payment.callback');
});

// Профиль
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');
});

require __DIR__ . '/admin.php';
require __DIR__ . '/auth_custom.php';
// require __DIR__ . '/auth.php';
