<?php

namespace Tests\Support\Payment;

use App\Support\Payment\Contracts\PaymentProviderContract;
use App\Support\Payment\PaymentData;
use App\Support\ValueObjects\Price;
use Illuminate\Http\JsonResponse;

/**
 * Тестовый платёжный провайдер по контракту PaymentProviderContract.
 * Полностью имитирует без сетевых вызовов — поведение задаётся
 * публичными свойствами, чтобы прогонять PaymentSystem по всем веткам.
 */
class FakePaymentProvider implements PaymentProviderContract
{
    protected ?PaymentData $paymentData = null;
    protected string $errorMessage = '';

    // --- Управляемое поведение (для разных сценариев тестов) ---

    /** Результат validate() (callback от провайдера прошёл проверку). */
    public bool $validates = true;

    /** Результат paid() (платёж оплачен). */
    public bool $isPaid = true;

    /** Транзакция, которую «вернёт» провайдер при create()/update(). */
    public string $transactionId = 'fake-tx-001';

    /** URL оплаты, который «вернёт» провайдер при create(). */
    public string $paymentUrl = 'https://fake.pay/redirect';

    /** «Сырое» тело callback-запроса. */
    public string $rawRequest = '{}';

    // --- Данные «уведомления», которые выставит update() ---

    public ?int $notifyOrderId = null;
    public ?Price $notifyAmount = null;
    public ?string $notifyDescription = 'Заказ (fake)';

    public function __construct(array $config = [])
    {
        $this->configure($config);
    }

    public function configure(array $config): void
    {
        // Тестовому провайдеру конфигурация не нужна.
    }

    public function setData(PaymentData $data): self
    {
        $this->paymentData = $data;

        return $this;
    }

    public function getData(): PaymentData
    {
        return $this->paymentData;
    }

    public function create(): void
    {
        // Имитация ответа провайдера: проставляем транзакцию и URL оплаты.
        $this->paymentData->transaction_id = $this->transactionId;
        $this->paymentData->payment_url = $this->paymentUrl;
    }

    public function update(): void
    {
        // Имитация данных, пришедших в callback от провайдера.
        $this->setData(new PaymentData(
            order_id: $this->notifyOrderId,
            transaction_id: $this->transactionId,
            description: $this->notifyDescription,
            return_url: null,
            payment_url: null,
            expired_at: null,
            amount: $this->notifyAmount,
            meta: collect(),
        ));
    }

    public function requestRaw(): string
    {
        return $this->rawRequest;
    }

    public function request(): mixed
    {
        return json_decode($this->rawRequest, true);
    }

    public function response(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function payload(): array
    {
        return ['fake' => true];
    }

    public function validate(): bool
    {
        return $this->validates;
    }

    public function paid(): bool
    {
        return $this->isPaid;
    }

    public function transactionId(): ?string
    {
        return $this->paymentData?->transaction_id;
    }

    public function paymentUrl(): ?string
    {
        return $this->paymentData?->payment_url;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    public function providerName(): string
    {
        return 'fake';
    }
}
