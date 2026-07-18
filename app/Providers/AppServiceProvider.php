<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        // Model::preventLazyLoading(!app()->isProduction());

        // Exception когда пытаещся присвоить значение в поле, которое не было добавлено в массив fillable модели
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Предотвращение доступа к отсутствующим атрибутам в восстановленных моделях (контроль null при агригации данных)
        Model::preventAccessingMissingAttributes(!app()->isProduction());

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

        RateLimiter::for('auth', function (Request $request) {
            $email = $this->throttledEmail($request);

            $limits = [
                Limit::perMinute(10)
                    ->by('ip|' . $request->ip())
                    // Сallback ответа при превышении лимита (иначе стандартный 429 ответ)
                    ->response(fn(Request $request, array $headers) => $this->lockoutResponse($request, $headers))
            ];

            if ($email !== '') {
                $limits[] = Limit::perMinute(20)
                    ->by('email|' . $email)
                    ->response(fn(Request $request, array $headers) => $this->lockoutResponse($request, $headers));
            }

            return $limits;
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Дефолтная валидация пароля
        Password::defaults(function () {
            return Password::min(8)->max(255);
        });
    }

    /**
     * Ключ лимита по аккаунту для маршрутов авторизации.
     */
    private function throttledEmail(Request $request): string
    {
        $email = $request->input('email');

        // Лимитер отрабатывает до валидации, поэтому здесь сырой ввод: email
        // может прийти массивом. Приведение такого значения к строке дало бы
        // общее для всех ограничение 'Array', поэтому ключа по аккаунту тогда нет.
        if (!is_string($email)) {
            return '';
        }

        // Без нормализации ' Test@VK.com ' и 'test@vk.com' дают разные ключи,
        // и лимит по аккаунту обходится одним пробелом в поле.
        return str($email)
            ->squish()
            ->lower()
            ->transliterate()
            ->value();
    }

    /**
     * Хук, срабатывающий на превышении лимита: отсюда шлём
     * Lockout, чтобы у блокировок был сигнал для алертинга.
     *
     * @param array{'Retry-After'?: int} $headers
     */
    private function lockoutResponse(Request $request, array $headers): RedirectResponse
    {
        event(new Lockout($request));

        $seconds = $headers['Retry-After'] ?? 60;

        return back()
            ->withErrors(['email' => "Слишком много попыток. Повторите через {$seconds} сек."])
            ->onlyInput('email');
    }
}
