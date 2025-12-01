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
    protected PaymentData $paymentData;
    protected string $errorMessage;

    public function __construct(array $config)
    {
        $this->client = new Client;
        $this->configure($config);
    }

    public function paymentId(): string
    {
        return $this->paymentData->payment_id;
    }

    public function configure(array $config): void
    {
        $this->client->setAuth($config['shop_id'], $config['key']);
    }

    public function data(PaymentData $data): self
    {
        $this->paymentData = $data;

        return $this;
    }

    public function request(): mixed
    {
        return json_decode(request()->getContent(), true);
    }

    /**
     * @return JsonResponse
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

    /**
     * @return string
     * @throws PaymentProviderException
     */
    public function url(): string
    {
        try {
            $response = $this->client->createPayment(
                $this->payload(),
                $this->idempotenceKey()
            );

            return $response
                ->getConfirmation()
                ->getConfirmationUrl();
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            throw new PaymentProviderException($e->getMessage());
        }
    }

    /**
     * @return bool
     * @throws PaymentProviderException
     */
    public function validate(): bool
    {
        $meta = $this->paymentObject()->getMetadata()->toArray();

        $this->data(new PaymentData(
            order_id: null,
            payment_id: $this->paymentObject()->getId(),
            payment_uuid: null,
            description: $this->paymentObject()->getDescription(),
            return_url: '',
            amount: Price::make(
                $this->paymentObject()->getAmount()->getIntegerValue(),
                $this->paymentObject()->getAmount()->getCurrency(),
            ),
            meta: collect($meta)
        ));

        return $this->paymentObject()->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE;
    }

    /**
     * @return bool
     * @throws PaymentProviderException
     */
    public function paid(): bool
    {
        return $this->paymentObject()->getPaid();
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    private function payload(): array
    {
        $metadata = [];

        foreach ($this->paymentData->meta as $item) {
            $prepare = [];
            $prepare = [
                'product_id' => $item->product_id,
                'title' => $item->product->title,
                'price' => $item->price->value(),
                'quantity' => $item->quantity,
            ];

            $metadata[] = $prepare;
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
            // 'capture' => true,
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
     * @return PaymentResponse|PaymentInterface
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
