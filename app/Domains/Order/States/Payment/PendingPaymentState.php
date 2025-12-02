<?php

namespace App\Domains\Order\States\Payment;

class PendingPaymentState extends PaymentState
{
    protected array $allowedTransitions = [
        PaidPaymentState::class,
        FailedPaymentState::class,
    ];

    public function value(): string
    {
        return 'pending';
    }

    public function humanValue(): string
    {
        return 'В обработке';
    }

    public function canBeChanged(): bool
    {
        return true;
    }
}
