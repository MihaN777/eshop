<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

//use Illuminate\Contracts\Http\Kernel;

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
        // Когда попытаетесь присвоить значение, который не был добавлен в массив fillable модели - exception
        // Контроль null при агригации данных из БД
        // Model::preventLazyLoading(!app()->isProduction());
        // Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        Model::shouldBeStrict(!app()->isProduction());

        // Мониторинг общего времени выполнения запроса
        //        DB::listen(function ($query) {
        //            if ($query->time > 100) {
        //                logger()
        //                    ->channel('telegram')
        //                    ->debug('whenQueryingForLongerThan: ' . $query->sql, $query->bindings);
        //            }
        //        });

        // Если запрос гуляет долго
        //        $kernel = app(Kernel::class);
        //        $kernel->whenRequestLifecyrcleIsLongerThan(
        //            CarbonInterval::seconds(4),
        //            function () {
        //                logger()
        //                    ->channel('telegram')
        //                    ->debug('whenRequestLifecyrcleIsLongerThan: ' . request()->url());
        //            });

        // Ограничение количества запросов
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(500)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('TOO_MANY_REQUESTS', Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
