<?php

namespace App\Providers;

use App\Domains\Cart\StorageIdentities\SessionIdentityStorage;
use App\Support\Cart\CartManager;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CartManager::class, function () {
            return new CartManager(new SessionIdentityStorage);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
