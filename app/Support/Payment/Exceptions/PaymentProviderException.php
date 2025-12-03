<?php

namespace App\Support\Payment\Exceptions;

use Exception;

class PaymentProviderException extends Exception
{
    public static function invalidProvider(): self
    {
        return new self('Invalid provider');
    }
}
