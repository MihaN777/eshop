<?php

namespace App\Support\Payment\Exceptions;

use Exception;

class PaymentProcessException extends Exception
{
    public static function validationFailed(): self
    {
        return new self('Validation failed');
    }

    public static function paymentModelsNotFound(): self
    {
        return new self('Payment models not found');
    }

    public static function createFailed($message): self
    {
        return new self("Create failed: {$message}");
    }

    public static function updateFailed($message): self
    {
        return new self("Update failed: {$message}");
    }
}
