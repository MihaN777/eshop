<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\SomeMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(SomeMiddleware::class);

//Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'can:admin-panel', 'verified']], function () {});
