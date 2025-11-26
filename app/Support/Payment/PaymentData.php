<?php

namespace App\Support\Payment;

use App\Support\ValueObjects\Price;
use Illuminate\Support\Collection;

class PaymentData
{
    public function __construct(
        public string     $id,
        public string     $description,
        public string     $return_url,
        public Price      $amount,
        public Collection $meta
    )
    {
    }
}
