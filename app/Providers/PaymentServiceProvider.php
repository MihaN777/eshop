<?php

namespace App\Providers;

use App\Support\Payment\PaymentSystem;
use Illuminate\Support\ServiceProvider;
use Throwable;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->singleton(PaymentSystem::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // try {
        //     PaymentSystem::provider(function () {
        //         if (request()->has('PaymentKassa'))
        //             return new PaymentKassa;
        //
        //         return new OtherPaymentKassa;
        //     });
        // } catch (Throwable $e) {
        //     logger()->error($e->getMessage());
        //     logger()->channel('telegram')->error($e->getMessage());
        //
        //     abort(500);
        // }

        // PaymentSystem::onCreating(function () {
        //
        // });
    }
}
