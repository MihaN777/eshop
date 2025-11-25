<?php

namespace App\Domains\Order\Processes;

use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class AssignCustomerProcess implements OrderProcessContract
{
    public function __construct(
        protected array $customer
    )
    {
    }

    public function handle(Order $order, mixed $next): mixed
    {
        $order->orderCustomer()->create($this->customer);
        $order->load(['orderCustomer']);

        return $next($order);
    }
}
