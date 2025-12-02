<?php

namespace App\Domains\Order\States\Payment;

class PaidPaymentState extends PaymentState
{
    protected array $allowedTransitions = [
        //
    ];

    public function value(): string
    {
        return 'paid';
    }

    public function humanValue(): string
    {
        return 'Оплачен';
    }

    public function canBeChanged(): bool
    {
        return false;
    }
}
