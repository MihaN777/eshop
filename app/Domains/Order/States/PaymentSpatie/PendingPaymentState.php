<?php

namespace App\Domains\Order\States\PaymentSpatie;

class PendingPaymentState extends PaymentState
{
    public static string $name = 'pending';

    public function color(): string
    {
        return 'grey';
    }
}
