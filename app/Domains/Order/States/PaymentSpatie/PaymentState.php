<?php

namespace App\Domains\Order\States\PaymentSpatie;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PaymentState extends State
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(PendingPaymentState::class)
            ->allowTransition(PendingPaymentState::class, PaidPaymentState::class)
            ->allowTransition(PendingPaymentState::class, FailedPaymentState::class);
    }
}
