<?php

namespace App\Services\Payments;

use App\Support\Payment\Contracts\PaymentProviderContract;
use App\Support\Payment\PaymentData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class YooKassa implements PaymentProviderContract
{
    protected const API_URL = 'https://yookassa.ru/api';
    protected const PAYMENT_URL = 'https://yookassa.ru/payment';

    protected PaymentData $paymentData;
    protected ?object $paymentObject;
    protected string $errorMessage;

    public function __construct(array $config = null)
    {
        if ($config) $this->configure($config);
    }

    public function configure(array $config): void
    {
        // TODO: Implement configure() method.
    }

    public function data(PaymentData $data): self
    {
        $this->paymentData = $data;

        return $this;
    }

    public function request(): mixed
    {
        $response = Http::post(self::API_URL, $this->paymentData->toJson());
        $this->paymentObject = $response->object();

        return $this->paymentObject;
    }

    public function response(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    public function paymentId(): string
    {
        return $this->paymentData->payment_id;
    }

    public function url(): string
    {
        return self::PAYMENT_URL;
    }

    public function validate(): bool
    {
        return true;
    }

    public function paid(): bool
    {
        return $this->paymentObject?->status === 'paid';
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }
}
