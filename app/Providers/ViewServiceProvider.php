<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Команда Vite::image для удобства добавления изображений во view
        Vite::macro('image', fn ($asset) => $this->asset("resources/images/{$asset}"));
    }
}
