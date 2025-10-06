<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

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

        // Мониторинг времени выполнения запроса
        //        DB::listen(function ($query) {
        //            if ($query->time > 100) {
        //                logger()
        //                    ->channel('telegram')
        //                    ->debug('Превышено время выполнения запроса: ' . $query->sql, $query->bindings);
        //            }
        //        });

        // Если запрос гуляет долго
        //        app(Kernel::class)->whenRequestLifecyrcleIsLongerThan(
        //            CarbonInterval::seconds(4),
        //            function () {
        //                logger()
        //                    ->channel('telegram')
        //                    ->debug('Долгий запрос: ' . request()->url());
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

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject(config('app.name') . ': Подтверждение электронной почты')
                ->line('Нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты.')
                ->action('Подтвердить', $url);
        });

        // TODO: Добавить русский перевод для письма сброса пароля
        //        ResetPassword::toMailUsing(function (object $notifiable, string $url) {
        //            return (new MailMessage)
        //                ->subject(config('app.name') . ': Обновление пароля')
        //                ->line('Вы получили это электронное письмо, потому что был получен запрос на обновление пароля для вашей учетной записи.')
        //                ->action('Обновить', $url)
        //                ->line('Срок действия этой ссылки для обновления пароля истечет через :count минут.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')])
        //                ->line('Если вы не запрашивали обновление пароля, никаких дальнейших действий не требуется.');
        //        });

        // Настройка валидации пароля пользователя
        //        Password::defaults(function () {
        //            // TODO
        //            return Password::min(8);
        //        });
    }
}
