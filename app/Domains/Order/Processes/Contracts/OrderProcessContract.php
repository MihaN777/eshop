<?php

namespace App\Domains\Order\Processes\Contracts;

use App\Models\Order;

interface OrderProcessContract
{
    public function handle(Order $order, mixed $next): mixed;
}
