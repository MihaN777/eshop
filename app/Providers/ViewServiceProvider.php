<?php

namespace App\Providers;

use App\Domains\Main\Menu\Menu;
use App\Domains\Main\Menu\MenuItem;
use Illuminate\Support\Facades\View;
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
        Vite::macro('image', fn($asset) => $this->asset("resources/images/{$asset}"));

        // Расшарить переменную на все шаблоны view
        View::composer('*', function ($view) {
            $view->with(
                [
                    'menu' => Menu::make()
                        ->add(MenuItem::make(route('home'), 'Главная'))
                        ->add(MenuItem::make(route('catalog'), 'Каталог')),
                ]
            );
        });
    }
}
