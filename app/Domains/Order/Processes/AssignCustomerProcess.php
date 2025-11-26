<?php

namespace App\Domains\Order\Processes;

use App\Actions\DTOs\OrderCustomerDTO;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class AssignCustomerProcess implements OrderProcessContract
{
    public function __construct(
        protected OrderCustomerDTO $dto
    )
    {
    }

    public function handle(Order $order, mixed $next): mixed
    {
        $order->orderCustomer()->create($this->dto->toArray());
        $order->load(['orderCustomer']);

        return $next($order);
    }
}
