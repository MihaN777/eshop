<?php

namespace App\Domains\Order\States;

class CancelledOrderState extends OrderState
{
    protected array $allowedTransitions = [
        //
    ];

    public function value(): string
    {
        return 'cancelled';
    }

    public function humanValue(): string
    {
        return 'Отменен';
    }

    public function canBeChanged(): bool
    {
        return false;
    }
}
