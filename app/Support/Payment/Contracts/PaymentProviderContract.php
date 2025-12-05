<?php

namespace App\Support\Payment\Contracts;

use App\Support\Payment\PaymentData;
use Illuminate\Http\JsonResponse;

interface PaymentProviderContract
{
    public function configure(array $config): void;

    public function setData(PaymentData $data): self;

    public function getData(): PaymentData;

    public function create(): void;

    public function update(): void;

    public function requestRaw(): string;

    public function request(): mixed;

    public function response(): JsonResponse;

    public function payload(): array;

    public function validate(): bool;

    public function paid(): bool;

    public function transactionId(): ?string;

    public function paymentUrl(): ?string;

    public function errorMessage(): string;

    public function providerName(): string;
}
