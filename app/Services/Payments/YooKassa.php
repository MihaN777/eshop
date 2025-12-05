<?php

namespace App\Services\Payments;

use App\Support\Payment\Contracts\PaymentProviderContract;
use App\Support\Payment\Exceptions\PaymentProviderException;
use App\Support\Payment\PaymentData;
use App\Support\ValueObjects\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Throwable;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Model\Payment\PaymentStatus;
use YooKassa\Request\Payments\PaymentResponse;

final class YooKassa implements PaymentProviderContract
{
    protected Client $client;
    protected ?PaymentData $paymentData;
    protected string $errorMessage;

    public function __construct(array $config)
    {
        $this->client = new Client;
        $this->paymentData = null;
        $this->errorMessage = '';

        $this->configure($config);
    }

    public function configure(array $config): void
    {
        $this->client->setAuth($config['shop_id'], $config['key']);
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

    /**
     * @throws PaymentProviderException
     */
    public function create(): void
    {
        try {
            $response = $this->client->createPayment(
                $this->payload(),
                $this->idempotenceKey()
            );

            $this->paymentData->transaction_id = $response->getId();
            $this->paymentData->payment_url = $response->getConfirmation()->getConfirmationUrl();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            throw new PaymentProviderException($e->getMessage());
        }
    }

    /**
     * @throws PaymentProviderException
     */
    public function update(): void
    {
        $paymentObject = $this->paymentObject();

        $this->setData(new PaymentData(
            order_id: null,
            transaction_id: $paymentObject->getId(),
            description: $paymentObject->getDescription(),
            return_url: null,
            payment_url: null,
            expired_at: null,
            amount: new Price(
                value: $paymentObject->getAmount()->getIntegerValue(),
                currency: $paymentObject->getAmount()->getCurrency(),
            ),
            meta: collect($paymentObject->getMetadata()->toArray())
        ));
    }

    public function requestRaw(): string
    {
        $requestBody = request()->getContent(); // file_get_contents('php://input');

        if (!is_string($requestBody)) $requestBody = '';

        return $requestBody;
    }

    public function request(): mixed
    {
        return json_decode($this->requestRaw(), true);
    }

    /**
     * @throws PaymentProviderException
     */
    public function response(): JsonResponse
    {
        try {
            $response = $this->client->capturePayment(
                $this->payload(),
                $this->paymentObject()->getId(),
                $this->idempotenceKey()
            );
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            throw new PaymentProviderException($e->getMessage());
        }

        return response()->json($response);
    }

    public function payload(): array
    {
        $metadata = [];
        foreach ($this->paymentData->meta as $item) {
            $metadata[] = [
                'product_id' => $item->product_id,
                'title' => $item->product->title,
                'price' => $item->price->value(),
                'quantity' => $item->quantity,
            ];
        }

        return [
            'amount' => [
                'value' => $this->paymentData->amount->value(),
                'currency' => $this->paymentData->amount->currency(),
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $this->paymentData->return_url,
            ],
            'capture' => true,
            'description' => $this->paymentData->description,
            'receipt' => [
                'items' => [
                    'quantity' => 1,
                    'amount' => [
                        'value' => $this->paymentData->amount->value(),
                        'currency' => $this->paymentData->amount->currency(),
                    ],
                    'vat_code' => 1,
                    'description' => $this->paymentData->description,
                    'payment_subject' => 'intellectual_activity',
                    'payment_mode' => 'full_payment',
                ],
                'tax_system_code' => 1,
                // 'email' => $this->paymentData->meta->get('email'),
            ],
            'metadata' => $metadata,
        ];
    }

    /**
     * @throws PaymentProviderException
     */
    public function validate(): bool
    {
        return $this->paymentObject()->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE;
    }

    /**
     * @throws PaymentProviderException
     */
    public function paid(): bool
    {
        return $this->paymentObject()->getPaid();
    }

    public function transactionId(): ?string
    {
        return $this->paymentData->transaction_id;
    }

    public function paymentUrl(): ?string
    {
        return $this->paymentData->payment_url;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    public function providerName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', self::class);
    }

    /**
     * @throws PaymentProviderException
     */
    private function paymentObject(): PaymentResponse|PaymentInterface
    {
        $request = $this->request();

        try {
            $notification = ($request['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
                ? new NotificationSucceeded($request)
                : new NotificationWaitingForCapture($request);
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            throw new PaymentProviderException($e->getMessage());
        }

        return $notification->getObject();
    }

    private function idempotenceKey(): string
    {
        return uniqid('', true);
    }
}
