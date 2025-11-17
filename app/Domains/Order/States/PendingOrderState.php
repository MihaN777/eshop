<?php

namespace App\Domains\Order\States;

class PendingOrderState extends OrderState
{
    protected array $allowedTransitions = [
        PaidOrderState::class,
        CancelledOrderState::class,
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
