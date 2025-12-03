<?php

namespace App\Support\Payment;

use App\Domains\Order\States\PaidOrderState;
use App\Domains\Order\States\Payment\PaidPaymentState;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Support\Payment\Contracts\PaymentProviderContract;
use App\Support\Payment\Exceptions\PaymentProcessException;
use App\Support\Payment\Exceptions\PaymentProviderException;
use App\Support\Payment\Traits\PaymentEvents;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentSystem
{
    use PaymentEvents;

    public static ?PaymentProviderContract $provider = null;

    /**
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
     * @throws PaymentProcessException
     * @throws PaymentProviderException
     */
    public static function create(Order $order): PaymentProviderContract
    {
        if (!self::$provider instanceof PaymentProviderContract) {
            throw PaymentProviderException::invalidProvider();
        }

        $paymentData = new PaymentData(
            order_id: $order->id,
            transaction_id: null, // str()->orderedUuid()->toString()
            description: "Заказ №{$order->id}",
            return_url: route('catalog'),
            payment_url: null,
            expired_at: null,
            amount: $order->amount,
            meta: $order->orderItems
        );

        if (is_callable(self::$onCreating)) {
            $paymentData = call_user_func(self::$onCreating, $paymentData);
        }

        try {
            // Создать заказ в платежной системе и получить данные транзакции
            self::$provider->setData($paymentData)->create();

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'transaction_id' => self::$provider->getData()->transaction_id,
                'payment_url' => self::$provider->getData()->payment_url,
                'expire_at' => self::$provider->getData()->expired_at,
                'provider' => self::$provider->providerName(),
                'meta' => $order->orderItems->toJson(),
            ]);
        } catch (Throwable $e) {
            if (is_callable(self::$onError)) {
                call_user_func(
                    self::$onError,
                    self::$provider->errorMessage() ?? $e->getMessage()
                );
            }

            throw PaymentProcessException::createFailed($e->getMessage());
        }

        if (is_callable(self::$onCreated)) {
            call_user_func(self::$onCreated, $payment);
        }

        return self::$provider;
    }


    /**
     * @throws PaymentProcessException
     * @throws PaymentProviderException
     */
    public static function validate(): PaymentProviderContract
    {
        if (!self::$provider instanceof PaymentProviderContract) {
            throw PaymentProviderException::invalidProvider();
        }

        $paymentHistory = PaymentHistory::query()->create([
            'provider' => self::$provider->providerName(),
            'method' => request()->method(),
            'payload' => self::$provider->request(),
        ]);

        if (is_callable(self::$onValidating)) {
            call_user_func(self::$onValidating);
        }

        if (!self::$provider->validate()) {
            throw PaymentProcessException::validationFailed();
        }

        try {
            // Получить данные транзакции по платежу
            self::$provider->update();

            DB::beginTransaction();

            $paymentHistory->transaction_id = self::$provider->getData()->transaction_id;
            $paymentHistory->description = !empty(self::$provider->getData()->description) ? self::$provider->getData()->description : null;
            $paymentHistory->save();

            $payment = Payment::query()
                ->with('order')
                ->where('provider', self::$provider->providerName())
                ->where('transaction_id', self::$provider->getData()->transaction_id)
                ->latest('id')
                ->first();

            $order = $payment?->order;

            if (!$payment || !$order) {
                throw PaymentProcessException::paymentModelsNotFound();
            }

            if (self::$provider->paid()) {
                $order->status->transitionTo(new PaidOrderState($order));

                $payment->status->transitionTo(new PaidPaymentState($payment));
                $payment->amount = self::$provider->getData()->amount->value();
                $payment->save();
            }

            DB::commit();

            if (is_callable(self::$onSuccess)) {
                call_user_func(self::$onSuccess, $payment);
            }

        } catch (Throwable $e) {
            DB::rollBack();

            if (is_callable(self::$onError)) {
                call_user_func(
                    self::$onError,
                    self::$provider->errorMessage() ?? $e->getMessage()
                );
            }

            throw PaymentProcessException::updateFailed($e->getMessage());
        }

        return self::$provider;
    }
}
