<?php

use App\Http\Controllers\Client\CatalogController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Каталог
Route::get('/catalog/{category:slug?}', [CatalogController::class, 'catalog'])->name('catalog');
Route::get('/catalog/product/{product}', [CatalogController::class, 'product'])->name('catalog.product');

// Профиль
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/delete', [ProfileController::class, 'delete'])->name('profile.delete');
});

require __DIR__ . '/admin.php';
require __DIR__ . '/auth_custom.php';
// require __DIR__ . '/auth.php';
