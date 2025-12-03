<?php

namespace App\Support\Payment\Exceptions;

use Exception;

class PaymentProviderException extends Exception
{
    public static function invalidProvider(): self
    {
        return new self('Invalid provider');
    }

    public static function validationFailed(): self
    {
        return new self('Validation failed');
    }
}
