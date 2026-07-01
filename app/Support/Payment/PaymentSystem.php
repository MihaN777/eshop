<?php

namespace App\Support\Payment;

use App\Domains\Order\Enums\PaymentStatuses;
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

        $providers = config(app()->isProduction() ? 'payment.providers.production' : 'payment.providers.testing', []);

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
                'meta' => self::$provider->payload(),
                'provider' => self::$provider->providerName(),
            ]);
        } catch (Throwable $e) {
            $errorMsg = self::$provider->errorMessage() ?? $e->getMessage();

            if (is_callable(self::$onError)) {
                call_user_func(self::$onError, $errorMsg);
            }

            throw PaymentProcessException::createFailed($errorMsg);
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
    public static function update(): PaymentProviderContract
    {
        if (!self::$provider instanceof PaymentProviderContract) {
            throw PaymentProviderException::invalidProvider();
        }

        $paymentHistory = PaymentHistory::query()->create([
            'provider' => self::$provider->providerName(),
            'payload' => self::$provider->requestRaw(),
            'request_ip' => request()->ip(),
            'method' => request()->method(),
            'validated' => 'No',
        ]);

        if (!self::$provider->validate()) {
            if (is_callable(self::$onValidatingFailed)) {
                call_user_func(self::$onValidatingFailed, $paymentHistory);
            }

            throw PaymentProcessException::validationFailed();
        }

        $paymentHistory->validated = 'Yes';
        $paymentHistory->save();

        try {
            // Получить данные транзакции по платежу
            self::$provider->update();
        } catch (Throwable $e) {
            $errorMsg = self::$provider->errorMessage() ?? $e->getMessage();

            if (is_callable(self::$onError)) {
                call_user_func(self::$onError, $errorMsg);
            }

            throw PaymentProcessException::updateFailed($errorMsg);
        }

        $paymentHistory->transaction_id = self::$provider->getData()->transaction_id;
        $paymentHistory->description = self::$provider->getData()->description;
        $paymentHistory->save();

        try {
            DB::beginTransaction();

            $payment = Payment::query()
                ->with('order')
                ->where('provider', self::$provider->providerName())
                ->when(isset(self::$provider->getData()->order_id),
                    // True
                    function ($query) {
                        return $query->where('order_id', self::$provider->getData()->order_id);
                    },
                    // False
                    function ($query) {
                        return $query->where('transaction_id', self::$provider->getData()->transaction_id);
                    }
                )
                ->latest('id')
                ->first();

            $order = $payment?->order;

            if (!$payment || !$order) {
                throw PaymentProcessException::paymentModelsNotFound();
            }

            // Идемпотентность вебхука: повторное уведомление по уже оплаченному
            // платежу не должно повторно менять состояние (переход состояние из Paid запрещён) и давать 500.
            if ($payment->status->value() === PaymentStatuses::Paid->value) {
                DB::commit();

                if (is_callable(self::$onSuccess)) {
                    call_user_func(self::$onSuccess, $payment);
                }

                return self::$provider;
            }

            if (self::$provider->paid()) {
                $providerAmount = self::$provider->getData()->amount;

                $payment->status->transitionTo(new PaidPaymentState($payment));
                $payment->amount = $providerAmount?->raw();
                $payment->save();

                if ($providerAmount?->rawEqualTo($order->amount)) {
                    $order->status->transitionTo(new PaidOrderState($order));
                }
            }

            DB::commit();

            if (is_callable(self::$onSuccess)) {
                call_user_func(self::$onSuccess, $payment);
            }

        } catch (Throwable $e) {
            DB::rollBack();

            $errorMsg = self::$provider->errorMessage() ?? $e->getMessage();

            if (is_callable(self::$onError)) {
                call_user_func(self::$onError, $errorMsg);
            }

            throw PaymentProcessException::updateFailed($errorMsg);
        }

        return self::$provider;
    }
}
