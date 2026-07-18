<?php

namespace App\Support\Payment;

use App\Domains\Order\Enums\OrderStatuses;
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
            // Транзакция с ретраем при дедлоке. Внутри берём тот же лок на строку
            // заказа, что и CancelUnpaidOrdersCommand, — иначе оплата и автоотмена
            // не взаимоисключаются и оплаченный заказ можно отменить.
            $payment = DB::transaction(function () {
                $payment = Payment::query()
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

                if (!$payment) {
                    throw PaymentProcessException::paymentModelsNotFound();
                }

                // Лок на заказ держится до конца транзакции: пока он наш,
                // команда отмены не сможет отменить заказ и вернуть остатки.
                $order = Order::query()
                    ->lockForUpdate()
                    ->find($payment->order_id);

                if (!$order) {
                    throw PaymentProcessException::paymentModelsNotFound();
                }

                // Статус платежа перечитываем уже под локом: параллельный вебхук
                // (ретрай платежного провайдера) мог оплатить его между выборкой и захватом лока.
                $payment->refresh();

                // Идемпотентность вебхука: повторное уведомление по уже оплаченному
                // платежу не меняет состояние (переход из Paid запрещён) и не даёт 500.
                if ($payment->status->value() === PaymentStatuses::Paid->value) {
                    return $payment;
                }

                if (self::$provider->paid()) {
                    $providerAmount = self::$provider->getData()->amount;

                    // Деньги получены — платёж помечаем оплаченным в любом случае
                    // (нужно для сверки и возможного возврата).
                    $payment->status->transitionTo(new PaidPaymentState($payment));
                    $payment->amount = $providerAmount?->raw();
                    $payment->save();

                    if ($order->status->value() === OrderStatuses::Pending->value) {
                        if ($providerAmount?->priceEqualTo($order->amount)) {
                            $order->status->transitionTo(new PaidOrderState($order));
                        }
                    } else {
                        // Заказ уже не Pending (например, отменён по гонке с автоотменой):
                        // оплату не откатываем, заказ не трогаем, но сигналим об аномалии.
                        report("Оплата по заказу #{$order->id} получена, но заказ в статусе «{$order->status->value()}» — требуется ручная обработка.");
                    }
                }

                return $payment;
            }, 3);

            if (is_callable(self::$onSuccess)) {
                call_user_func(self::$onSuccess, $payment);
            }
        } catch (Throwable $e) {
            $errorMsg = self::$provider->errorMessage() ?? $e->getMessage();

            if (is_callable(self::$onError)) {
                call_user_func(self::$onError, $errorMsg);
            }

            throw PaymentProcessException::updateFailed($errorMsg);
        }

        return self::$provider;
    }
}
