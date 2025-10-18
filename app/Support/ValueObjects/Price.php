<?php

namespace App\Support\ValueObjects;

use App\Support\Traits\Makeable;
use InvalidArgumentException;
use Stringable;

final class Price implements Stringable
{
    use Makeable;

    private array $currencies = [
        'RUB' => '₽',
        'USD' => '$',
    ];

    private readonly int $value;
    private readonly int $precision;
    private readonly string $currency;

    public function __construct($value, $precision = 1, $currency = 'RUB')
    {
        if ($value < 0) throw new InvalidArgumentException('Цена должна быть больше нуля');

        if (!isset($this->currencies[$currency])) throw new InvalidArgumentException("Валюта {$currency} не поддерживается");

        $this->value = $value;
        $this->precision = $precision;
        $this->currency = $currency;
    }

    public function raw(): int
    {
        return $this->value;
    }

    public function value(): float|int
    {
        return $this->value / $this->precision;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function symbol(): string
    {
        return $this->currencies[$this->currency];
    }

    public function __toString(): string
    {
        return number_format($this->value(), 0, ',', ' ') . ' ' . $this->symbol();
    }
}
