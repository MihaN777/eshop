<?php

use App\Http\Controllers\Admin\HomeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('admin.home');
});
