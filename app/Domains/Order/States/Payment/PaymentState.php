<?php

namespace App\Domains\Order\States\Payment;

use App\Models\Payment;
use InvalidArgumentException;

abstract class PaymentState
{
    protected array $allowedTransitions = [];

    public function __construct(
        protected Payment $payment
    )
    {
    }

    abstract public function value(): string;

    abstract public function humanValue(): string;

    abstract public function canBeChanged(): bool;

    public function transitionTo(PaymentState $state): void
    {
        if (!$this->canBeChanged())
            throw new InvalidArgumentException('Статус не может быть изменен');

        if (!in_array(get_class($state), $this->allowedTransitions))
            throw new InvalidArgumentException("Не возможно изменить статус {$this->payment->status->value()} в статус {$state->value()}");

        $this->payment->updateQuietly(['status' => $state->value()]);
    }
}
