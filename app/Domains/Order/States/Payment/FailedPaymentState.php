<?php

namespace App\Domains\Order\States\Payment;

class FailedPaymentState extends PaymentState
{
    public static string $name = 'failed';

    public function color(): string
    {
        return 'red';
    }
}
