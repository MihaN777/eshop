<?php

namespace App\Domains\Order\States;

class PaidOrderState extends OrderState
{
    protected array $allowedTransitions = [
        CancelledOrderState::class,
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
        return true;
    }
}
