<?php

namespace App\Support\Payment;

use App\Support\ValueObjects\Price;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class PaymentData
{
    public function __construct(
        public ?int        $order_id,
        public ?string     $transaction_id,
        public ?string     $description,
        public ?string     $return_url,
        public ?string     $payment_url,
        public ?Carbon     $expired_at,
        public ?Price      $amount,
        public ?Collection $meta
    )
    {
    }

    public function toCollection(): Collection
    {
        return collect([
            'order_id' => $this->order_id,
            'transaction_id' => $this->transaction_id,
            'description' => $this->description,
            'return_url' => $this->return_url,
            'payment_url' => $this->payment_url,
            'expired_at' => $this->expired_at?->toString(),
            'amount' => $this->amount?->raw(),
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
