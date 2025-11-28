<?php

namespace App\Support\Payment;

use App\Support\ValueObjects\Price;
use Illuminate\Support\Collection;

final class PaymentData
{
    public function __construct(
        public readonly int        $order_id,
        public readonly ?string    $pay_id,
        public readonly string     $description,
        public readonly string     $return_url,
        public readonly Price      $amount,
        public readonly Collection $meta
    )
    {
    }

    public function toCollection(): Collection
    {
        return collect([
            'order_id' => $this->order_id,
            'pay_id' => $this->pay_id,
            'description' => $this->description,
            'return_url' => $this->return_url,
            'amount' => $this->amount,
            'meta' => $this->meta,
        ]);
    }

    public function toArray(): array
    {
        return $this->toCollection()->toArray();
    }

    public function toJson(): string
    {
        return $this->toCollection()->toJson();
    }
}
