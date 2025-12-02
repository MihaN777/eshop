<?php

namespace App\Domains\Order\Enums;

use App\Domains\Order\States\Payment\FailedPaymentState;
use App\Domains\Order\States\Payment\PaidPaymentState;
use App\Domains\Order\States\Payment\PaymentState;
use App\Domains\Order\States\Payment\PendingPaymentState;
use App\Models\Payment;

enum PaymentStatuses: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';

    public function createState(Payment $payment): PaymentState
    {
        return match ($this) {
            PaymentStatuses::Pending => new PendingPaymentState($payment),
            PaymentStatuses::Paid => new PaidPaymentState($payment),
            PaymentStatuses::Failed => new FailedPaymentState($payment),
        };
    }
}
