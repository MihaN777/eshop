<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Отключить отложенную загрузку
        Model::preventLazyLoading(!app()->isProduction());

        // Когда попытаетесь присвоить значение, который не был добавлен в массив fillable модели - exception
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Мониторинг общего времени выполнения запроса
        DB::whenQueryingForLongerThan(500, function (Connection $connection, QueryExecuted $event) {
            // TODO log
        });
    }
}
