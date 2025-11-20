<?php

namespace App\Domains\Order\Processes;

use App\Actions\DTOs\OrderCreateDTO;
use App\Domains\Order\Processes\Contracts\OrderProcessContract;
use App\Models\Order;

class AssignCustomerProcess implements OrderProcessContract
{
    public function __construct(
        protected OrderCreateDTO $dto
    )
    {
    }

    public function handle(Order $order, mixed $next): mixed
    {
        $order->orderCustomer()->create($this->dto->customer);

        return $next($order);
    }
}
