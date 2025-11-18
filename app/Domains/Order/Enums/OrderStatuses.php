<?php

namespace App\Domains\Order\Enums;

use App\Domains\Order\States\CancelledOrderState;
use App\Domains\Order\States\NewOrderState;
use App\Domains\Order\States\OrderState;
use App\Domains\Order\States\PaidOrderState;
use App\Domains\Order\States\PendingOrderState;
use App\Models\Order;

enum OrderStatuses: string
{
    case New = 'new';
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function createState(Order $order): OrderState
    {
        return match ($this) {
            OrderStatuses::New => new NewOrderState($order),
            OrderStatuses::Pending => new PendingOrderState($order),
            OrderStatuses::Paid => new PaidOrderState($order),
            OrderStatuses::Cancelled => new CancelledOrderState($order),
        };
    }
}
