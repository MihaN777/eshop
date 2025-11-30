<?php

namespace App\Support\Payment\Contracts;

use App\Support\Payment\PaymentData;
use Illuminate\Http\JsonResponse;

interface PaymentProviderContract
{
    public function paymentId(): string;

    public function configure(array $config): void;

    public function data(PaymentData $data): self;

    public function request(): mixed;

    public function response(): JsonResponse;

    public function url(): string;

    public function validate(): bool;

    public function paid(): bool;

    public function errorMessage(): string;
}
