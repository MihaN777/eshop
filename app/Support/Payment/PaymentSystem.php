<?php

namespace App\Support\Payment;

use App\Domains\Order\States\PaidOrderState;
use App\Domains\Order\States\Payment\PaidPaymentState;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Support\Payment\Contracts\PaymentProviderContract;
use App\Support\Payment\Exceptions\PaymentProcessException;
use App\Support\Payment\Exceptions\PaymentProviderException;
use App\Support\Payment\Traits\PaymentEvents;
use Closure;
use Illuminate\Support\Facades\DB;

class PaymentSystem
{
    use PaymentEvents;

    public static PaymentProviderContract $provider;

    /**
     * @param PaymentProviderContract|Closure|null $providerOrClosure
     * @return void
     * @throws PaymentProviderException
     */
    public static function setProvider(PaymentProviderContract|Closure|null $providerOrClosure): void
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
     * @param string $provider
     * @return void
     * @throws PaymentProviderException
     */
    public static function setProviderByName(string $provider): void
    {
        $provider = str($provider)
            ->slug('_')
            ->value();

        $providers = config('payment.providers', []);

        if (!array_key_exists($provider, $providers)) {
            throw PaymentProviderException::invalidProvider();
        }

        $providerClass = isset($providers[$provider]['class']) && !empty($providers[$provider]['class'])
            ? $providers[$provider]['class']
            : null;

        if (!class_exists($providerClass)) {
            throw PaymentProviderException::invalidProvider();
        }

        $providerInstance = new $providerClass($providers[$provider]);

        self::setProvider($providerInstance);
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
            'provider' => self::$provider->providerName(),
            'meta' => $paymentData->meta->toJson(),
        ]);

        $paymentData->payment_id = $payment->id;

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
            'provider' => self::$provider->providerName(),
            'method' => request()->method(),
            'payload' => self::$provider->request(),
        ]);

        if (is_callable(self::$onValidating)) {
            call_user_func(self::$onValidating);
        }

        if (self::$provider->validate() && self::$provider->paid()) {
            try {
                DB::beginTransaction();

                $payment = Payment::query()
                    ->with('order')
                    ->where('transaction_id', self::$provider->transactionId())
                    ->firstOr(function () {
                        throw PaymentProcessException::paymentNotFound();
                    });
                $order = $payment->order;

                $payment->status->transitionTo(new PaidPaymentState($payment));
                $order->status->transitionTo(new PaidOrderState($order));

                DB::commit();

                if (is_callable(self::$onSuccess)) {
                    call_user_func(self::$onSuccess, $payment);
                }

            } catch (PaymentProcessException $e) {
                // Order cancelled
                DB::rollBack();

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
