<?php

namespace App\Domains\Order\States\PaymentSpatie;

class PaidPaymentState extends PaymentState
{
    public static string $name = 'paid';

    public function color(): string
    {
        return 'green';
    }
}
