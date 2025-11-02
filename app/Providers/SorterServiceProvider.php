<?php

namespace App\Providers;

use App\Support\Sorters\Sorter;
use Illuminate\Support\ServiceProvider;

class SorterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Sorter::class, function () {
            return new Sorter([
                'title',
                'price',
            ]);
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
