<?php

namespace App\Providers;

use App\Filters\BrandFilter;
use App\Filters\PriceFilter;
use App\Support\Filters\FilterManager;
use Illuminate\Support\ServiceProvider;

class FiltersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FilterManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        app(FilterManager::class)->registerFilters([
            new PriceFilter,
            new BrandFilter,
        ]);
    }
}
