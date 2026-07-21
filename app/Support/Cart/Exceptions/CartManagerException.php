<?php

namespace App\Support\Cart\Exceptions;

use Exception;

class CartManagerException extends Exception
{
    public static function exceededQuantityLimit(int $limit): self
    {
        return new self("Больше {$limit} шт. одного товара заказать нельзя.");
    }
}
