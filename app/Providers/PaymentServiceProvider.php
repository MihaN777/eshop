<?php

namespace App\Providers;

use App\Models\Payment;
use App\Services\Payments\UnitPay;
use App\Services\Payments\WebKassa;
use App\Support\Payment\PaymentData;
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
        try {
            PaymentSystem::provider(function () {
                if (request()->has('web_kassa'))
                    return new WebKassa(config('payment.providers.web_kassa'));

                return new UnitPay(config('payment.providers.unit_pay'));
            });
        } catch (Throwable $e) {
            logger()->error($e->getMessage());
            logger()->channel('telegram')->error($e->getMessage());

            abort(500);
        }

        PaymentSystem::onCreating(function (PaymentData $paymentData) {
            return $paymentData;
        });

        PaymentSystem::onSuccess(function (Payment $payment) {
            //
        });

        PaymentSystem::onError(function (Payment $payment, string $message) {
            //
        });
    }
}
