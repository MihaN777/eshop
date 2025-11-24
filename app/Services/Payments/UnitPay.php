<?php

namespace App\Services\Payments;

use App\Support\Payment\Contracts\PaymentGatewayContract;
use App\Support\Payment\PaymentData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class UnitPay implements PaymentGatewayContract
{
    protected PaymentData $paymentData;

    protected string $errorMessage = '';

    public function __construct(array $config = null)
    {
        if ($config) $this->configure($config);
    }

    public function paymentId(): string
    {
        return $this->paymentData->id;
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
        return json_decode('{"foo": "bar"}', true);

    }

    public function response(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    public function url(): string
    {
        return 'https://unit-pay.com/payment';
    }

    public function validate(): bool
    {
        if (false) return false;

        return true;
    }

    public function paid(): bool
    {
        return true;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }
}
