<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        Model::preventLazyLoading(!app()->isProduction());

        // Когда попытаетесь присвоить значение, который не был добавлен в массив fillable модели - exception
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Мониторинг общего времени выполнения запроса
        DB::whenQueryingForLongerThan(500, function (Connection $connection) {
            logger()
                ->channel('telegram')
                ->debug('whenQueryingForLongerThan: ' . $connection->query()->toSql());
        });

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
