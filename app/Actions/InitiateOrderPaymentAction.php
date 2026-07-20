<?php

namespace App\Actions;

use App\Models\Order;
use App\Support\Payment\Exceptions\PaymentProcessException;
use App\Support\Payment\Exceptions\PaymentProviderException;
use App\Support\Payment\PaymentSystem;

class InitiateOrderPaymentAction
{
    /**
     * Инициирует оплату ВНЕ транзакции создания заказа (после COMMIT).
     * Возвращает payment_url для редиректа или null, если оплата не требуется.
     *
     * @throws PaymentProcessException
     * @throws PaymentProviderException
     *
     */
    public function __invoke(Order $order, string $provider): ?string
    {
        if (!$order->paymentMethod->redirect_to_pay) {
            return null;
        }

        // При повторном сабмите того же заказа переиспользуем
        // «живой» pending-платёж вместо создания дубля.
        $existing = $order->payments()
            ->where('status', 'pending')
            ->whereNotNull('payment_url')
            ->where(fn($query) => $query->whereNull('expire_at')->orWhere('expire_at', '>', now()))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing->payment_url;
        }

        PaymentSystem::setProviderByName($provider);

        return PaymentSystem::create($order)->paymentUrl();
    }
}
