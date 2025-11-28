<?php

namespace App\Support\Payment;

use App\Domains\Order\States\Payment\PaidPaymentState;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Support\Payment\Contracts\PaymentProviderContract;
use App\Support\Payment\Exceptions\PaymentProcessException;
use App\Support\Payment\Exceptions\PaymentProviderException;
use App\Support\Payment\Traits\PaymentEvents;
use Closure;

class PaymentSystem
{
    use PaymentEvents;

    public static PaymentProviderContract $provider;

    /**
     * @param PaymentProviderContract|Closure $providerOrClosure
     * @return void
     * @throws PaymentProviderException
     */
    public static function provider(PaymentProviderContract|Closure $providerOrClosure): void
    {
        if (is_callable($providerOrClosure)) {
            $providerOrClosure = call_user_func($providerOrClosure);
        }

        if (!$providerOrClosure instanceof PaymentProviderContract) {

            throw PaymentProviderException::invalidProvider();
        }

        self::$provider = $providerOrClosure;
    }

    /**
     * @param PaymentData $paymentData
     * @return PaymentProviderContract
     * @throws PaymentProviderException
     */
    public static function create(PaymentData $paymentData): PaymentProviderContract
    {
        if (!self::$provider instanceof PaymentProviderContract) {
            throw PaymentProviderException::invalidProvider();
        }

        $payment = Payment::query()->create([
            'order_id' => $paymentData->order_id,
            'payment_provider' => get_class(self::$provider),
            'meta' => $paymentData->meta->toJson(),
        ]);

        $paymentData->payment_id = $payment->id;
        $paymentData->payment_uuid = $payment->uuid;

        if (is_callable(self::$onCreating)) {
            $paymentData = call_user_func(self::$onCreating, $paymentData);
        }

        return self::$provider->data($paymentData);
    }

    /**
     * @return PaymentProviderContract
     * @throws PaymentProviderException
     */
    public static function validate(): PaymentProviderContract
    {
        if (!self::$provider instanceof PaymentProviderContract) {
            throw PaymentProviderException::invalidProvider();
        }

        PaymentHistory::query()->create([
            'payment_gateway' => get_class(self::$provider),
            'method' => request()->method(),
            'payload' => self::$provider->request(),
        ]);

        if (is_callable(self::$onValidating)) {
            call_user_func(self::$onValidating);
        }

        if (self::$provider->validate() && self::$provider->paid()) {
            try {
                $payment = Payment::query()
                    ->where('payment_id', self::$provider->paymentId())
                    ->firstOr(function () {
                        throw PaymentProcessException::paymentNotFound();
                    });

                if (is_callable(self::$onSuccess)) {
                    call_user_func(self::$onSuccess, $payment);
                }

                $payment->state->transitionTo(PaidPaymentState::class);

            } catch (PaymentProcessException $e) {
                // Order cancelled

                if (is_callable(self::$onError)) {
                    call_user_func(
                        self::$onError,
                        self::$provider->errorMessage() ?? $e->getMessage()
                    );
                }
            }
        }

        return self::$provider;
    }
}
