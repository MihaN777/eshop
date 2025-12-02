<?php

namespace App\Domains\Order\States\Payment;

class FailedPaymentState extends PaymentState
{
    protected array $allowedTransitions = [
        //
    ];

    public function value(): string
    {
        return 'failed';
    }

    public function humanValue(): string
    {
        return 'Ошибка';
    }

    public function canBeChanged(): bool
    {
        return false;
    }
}
