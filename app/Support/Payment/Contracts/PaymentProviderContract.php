<?php

namespace App\Support\Payment\Contracts;

use App\Support\Payment\PaymentData;
use Illuminate\Http\JsonResponse;

interface PaymentProviderContract
{
    /**
     * Метод задает первоначальную конфигурацию платежному провайдеру.
     */
    public function configure(array $config): void;

    public function setData(PaymentData $data): self;

    public function getData(): PaymentData;

    /**
     * Метод создает заказ в платежном провайдере
     * и записывает принятые от платежного провайдера данные в DTO paymentData.
     */
    public function create(): void;

    /**
     * Метод получает данные по заказу от платежного провайдера
     * и записывает принятые от платежного провайдера данные в DTO paymentData.
     */
    public function update(): void;

    /**
     * Метод получает "сырые данные" из реквеста от от платежного провайдера.
     */
    public function requestRaw(): string;

    /**
     * Метод получает данные из реквеста от платежного провайдера в виде ассоциативного массива.
     */
    public function request(): mixed;

    /**
     * Метод формирует JsonResponse для отправки платежному провайдеру в качестве ответа.
     * Вызывается в callback от платежного провайдела.
     */
    public function response(): JsonResponse;

    /**
     * Метод формирует ассоциативный массив полезной нагрузки из DTO paymentData
     * для создания данных заказа и последующей отправки этих данных в платежный провайдер.
     */
    public function payload(): array;

    /**
     * Метод валидирует данные от платежного провайдера принятые из метода request().
     * Вызывается в callback от платежного провайдела.
     */
    public function validate(): bool;

    /**
     * Метод возвращает флаг куплености от платежного провайдера.
     */
    public function paid(): bool;

    /**
     * Метод возвращает ID транзакции заказа зарегистрированного у платежного провайдера.
     */
    public function transactionId(): ?string;

    /**
     * Метод возвращает платежный URL заказа зарегистрированного у платежного провайдера.
     */
    public function paymentUrl(): ?string;

    public function errorMessage(): string;

    public function providerName(): string;
}
