<?php

namespace App\Support\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Единый шлюз работы с деньгами.
 *
 * Внутри всегда хранит целые минорные единицы (копейки/центы) и валюту — это исключает
 * ошибки округления float и двусмысленность единиц. Все входы/выходы идут
 * через явные фабрики fromMajor()/fromMinor(), арифметика и сравнение — через методы,
 * чтобы исключить неявное обращение.
 */
final class Price implements Stringable
{
    private readonly int $minor;

    private readonly string $currency;

    private function __construct(int $minor, string $currency)
    {
        if ($minor < 0) {
            throw new InvalidArgumentException('Цена должна быть больше нуля');
        }

        if (!array_key_exists($currency, self::currencies())) {
            throw new InvalidArgumentException("Валюта {$currency} не поддерживается");
        }

        $this->minor = $minor;
        $this->currency = $currency;
    }

    /**
     * Из минорных единиц (копеек) — значение ложится в $minor как есть, без конвертации.
     *
     * @param int|null $minor Сумма в копейках
     */
    public static function fromMinor(?int $minor, ?string $currency = null): self
    {
        return new self($minor ?? 0, $currency ?? self::defaultCurrency());
    }

    /**
     * Из мажорных единиц (рублей) — с конвертацией в копейки.
     *
     * Основной вход для цен из БД (decimal-колонки в рублях) и пользовательского
     * ввода. Значение умножается на scaleMultiplier валюты (для ₽ — на 100) и один раз
     * округляется до целых копеек: "1000.50" → 100050. Дальше копейки не покидают
     * int, поэтому накопления погрешности float не происходит.
     *
     * @param int|float|string|null $major Сумма в рублях
     */
    public static function fromMajor(int|float|string|null $major, ?string $currency = null): self
    {
        $currency = $currency ?? self::defaultCurrency();

        $minor = (int)round(((float)($major ?? 0)) * self::scaleMultiplier($currency));

        return new self($minor, $currency);
    }

    /**
     * Алиас fromMajor() для обратной совместимости.
     */
    public static function make(int|float|string|null $major = 0, ?string $currency = null): self
    {
        return self::fromMajor($major, $currency);
    }

    public static function zero(?string $currency = null): self
    {
        return new self(0, $currency ?? self::defaultCurrency());
    }

    /**
     * Сумма в минорных единицах (копейках).
     */
    public function minor(): int
    {
        return $this->minor;
    }

    /**
     * Сумма в мажорных единицах (рублях).
     */
    public function value(): float|int
    {
        $result = $this->minor / self::scaleMultiplier($this->currency);

        return is_float($result) ? round($result, self::scale($this->currency)) : $result;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function symbol(): string
    {
        return self::currencies()[$this->currency]['symbol'];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function plus(self $price): self
    {
        $this->assertSameCurrency($price);

        return new self($this->minor + $price->minor, $this->currency);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function minus(self $price): self
    {
        $this->assertSameCurrency($price);

        return new self($this->minor - $price->minor, $this->currency);
    }

    /**
     * Умножает сумму на целочисленный множитель (цена × количество).
     */
    public function multiply(int $factor): self
    {
        return new self($this->minor * $factor, $this->currency);
    }

    /**
     * @param iterable<Price> $prices
     */
    public static function sum(iterable $prices, ?string $currency = null): self
    {
        $sum = self::zero($currency);

        foreach ($prices as $price) {
            $sum = $sum->plus($price);
        }

        return $sum;
    }

    public function equals(self $price): bool
    {
        if (!$this->currencyEqualTo($price)) {
            return false;
        }

        return $this->minor === $price->minor;
    }

    public function currencyEqualTo(self $price): bool
    {
        return $this->currency === $price->currency;
    }

    public function __toString(): string
    {
        $format = config("money.format.{$this->currency}");
        $decimals = strlen(explode('.', (string)$this->value())[1] ?? '');

        return number_format($this->value(), $decimals, $format['decimals'], $format['thousands'])
            . ' ' . $this->symbol();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertSameCurrency(self $price): void
    {
        if (!$this->currencyEqualTo($price)) {
            throw new InvalidArgumentException(
                "Нельзя оперировать разными валютами: {$this->currency} и {$price->currency}"
            );
        }
    }

    /**
     * @return array<string, array{symbol: string, scale: int}>
     */
    private static function currencies(): array
    {
        return config('money.currencies', []);
    }

    private static function defaultCurrency(): string
    {
        return config('money.default_currency', 'RUB');
    }

    /**
     * Число знаков минорной единицы валюты (для ₽/$ — 2: копейки/центы).
     */
    private static function scale(string $currency): int
    {
        return self::currencies()[$currency]['scale'] ?? 2;
    }

    /**
     * Множитель перевода мажорных единиц в минорные: 10^scale (для ₽ — 100).
     */
    private static function scaleMultiplier(string $currency): int
    {
        return 10 ** self::scale($currency);
    }
}
