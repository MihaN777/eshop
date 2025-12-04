<?php

namespace App\Providers;

use App\Models\Payment;
use App\Services\Payments\YooKassa;
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
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // try {
        //     PaymentSystem::setProvider(function () {
        //         // if (request()->has('unit_pay'))
        //         //     return new UnitPay(config('payment.providers.unit_pay'));
        //
        //         return new YooKassa(config('payment.providers.yoo_kassa'));
        //     });
        // } catch (Throwable $e) {
        //     logger()->error($e->getMessage());
        //     logger()->channel('telegram')->error($e->getMessage());
        // }

        PaymentSystem::onCreating(function (PaymentData $paymentData) {
            return $paymentData;
        });

        PaymentSystem::onCreated(function (Payment $payment) {
            //
        });

        PaymentSystem::onValidatingFailed(function (string $requestRaw) {
            //
        });

        PaymentSystem::onSuccess(function (Payment $payment) {
            //
        });

        PaymentSystem::onError(function (string $message) {
            //
        });
    }
}
