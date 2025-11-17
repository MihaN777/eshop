<?php

namespace App\Domains\Order\States;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use InvalidArgumentException;

abstract class OrderState
{
    protected array $allowedTransitions = [];

    public function __construct(
        protected Order $order
    )
    {
    }

    abstract public function value(): string;

    abstract public function humanValue(): string;

    abstract public function canBeChanged(): bool;

    public function transitionTo(OrderState $state): void
    {
        if (!$this->canBeChanged())
            throw new InvalidArgumentException('Статус не может быть изменен');

        if (!in_array(get_class($state), $this->allowedTransitions))
            throw new InvalidArgumentException("Не возможно изменить статус {$this->order->status->value()} в статус {$state->value()}");

        $this->order->updateQuietly(['status' => $state->value()]);

        event(new OrderStatusChanged($this->order, $this->order->status, $state));
    }
}
