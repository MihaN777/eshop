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

    private readonly float|int $value;
    private readonly float|int $precision;
    private readonly string $currency;

    public function __construct(null|float|int $value, float|int $precision = 1, string $currency = 'RUB')
    {
        if (is_null($value)) $value = 0;

        if ($value < 0) throw new InvalidArgumentException('Цена должна быть больше нуля');

        if (!isset($this->currencies[$currency])) throw new InvalidArgumentException("Валюта {$currency} не поддерживается");

        $this->value = $value;
        $this->precision = $precision;
        $this->currency = $currency;
    }

    public function raw(): float|int
    {
        return $this->value;
    }

    public function roundRaw(): float|int
    {
        return is_float($this->value) ? round($this->value, 2) : $this->value;
    }

    public function value(): float|int
    {
        $result = $this->value / $this->precision;

        return is_float($result) ? round($result, 2) : $result;
    }

    public function precision(): float|int
    {
        return $this->precision;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function symbol(): string
    {
        return $this->currencies[$this->currency];
    }

    public function equalRawTo(Price $price): bool
    {
        return $this->raw() === $price->raw();
    }

    public function equalValueTo(Price $price): bool
    {
        return $this->value() === $price->value();
    }

    public function equalPriceTo(Price $price): bool
    {
        return ($this->currency() === $price->currency()) && ($this->value() === $price->value());
    }

    public function __toString(): string
    {
        return number_format($this->value(), is_float($this->value()) ? 2 : 0, ',', ' ') . ' ' . $this->symbol();
    }
}
