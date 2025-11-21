<?php

namespace App\Domains\Order\States\Payment;

class PendingPaymentState extends PaymentState
{
    public static string $name = 'pending';

    public function color(): string
    {
        return 'grey';
    }
}
