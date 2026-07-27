<?php

namespace Tests\Unit\Support\ValueObjects;

use App\Support\ValueObjects\Price;
use InvalidArgumentException;
use Tests\TestCase;

// Price читает config('money.*'), поэтому нужен загруженный контейнер приложения.
uses(TestCase::class);

// --- Фабрики ---

it('строит из мажорных единиц (рублей) и хранит минорные (копейки)', function () {
    $price = Price::fromMajor(100);

    expect($price->minor())->toBe(10000)
        ->and($price->value())->toBe(100)
        ->and($price->currency())->toBe('RUB');
});

it('строит из минорных единиц (копеек)', function () {
    expect(Price::fromMinor(10050)->value())->toBe(100.5)
        ->and(Price::fromMinor(10000)->value())->toBe(100);
});

it('make() — алиас fromMajor()', function () {
    expect(Price::make(100)->minor())->toBe(Price::fromMajor(100)->minor());
});

it('zero() даёт нулевую сумму в валюте по умолчанию', function () {
    expect(Price::zero()->minor())->toBe(0)
        ->and(Price::zero()->currency())->toBe('RUB');
});

it('корректно переводит дробные рубли в копейки', function (float $rubles, int $minor) {
    expect(Price::fromMajor($rubles)->minor())->toBe($minor);
})->with([
    'целое' => [1000.0, 100000],
    'с копейками' => [1000.5, 100050],
    'две копейки' => [1000.55, 100055],
    'округление' => [0.014, 1],
]);

// --- Арифметика ---

it('складывает суммы', function () {
    expect(Price::fromMajor(100)->plus(Price::fromMajor(50))->value())->toBe(150);
});

it('умножает на количество', function () {
    expect(Price::fromMajor(100)->multiply(3)->value())->toBe(300);
});

it('суммирует коллекцию цен', function () {
    $sum = Price::sum([Price::fromMajor(10), Price::fromMajor(20), Price::fromMajor(30)]);

    expect($sum->value())->toBe(60);
});

it('сумма пустой коллекции равна нулю', function () {
    expect(Price::sum([])->value())->toBe(0);
});

it('запрещает арифметику разных валют', function () {
    Price::fromMajor(100, 'RUB')->plus(Price::fromMajor(100, 'USD'));
})->throws(InvalidArgumentException::class);

// --- Сравнение ---

it('сравнивает с точностью до копеек', function () {
    expect(Price::fromMajor(1000.57)->equals(Price::fromMajor(1000.57)))->toBeTrue()
        ->and(Price::fromMinor(100000)->equals(Price::fromMajor(1000)))->toBeTrue()
        ->and(Price::fromMajor(1000.55)->equals(Price::fromMajor(1000.54)))->toBeFalse();
});

it('разные валюты никогда не равны', function () {
    expect(Price::fromMajor(100, 'RUB')->equals(Price::fromMajor(100, 'USD')))->toBeFalse();
});

// --- Форматирование ---

it('выводит с копейками и без копеек', function () {
    expect((string)Price::fromMajor(1000.57))->toBe('1 000,57 ₽')
        ->and((string)Price::fromMajor(100.07))->toBe('100,07 ₽')
        ->and((string)Price::fromMajor(100.70))->toBe('100,7 ₽')
        ->and((string)Price::fromMajor(100.00))->toBe('100 ₽');
});

// --- Guard'ы ---

it('запрещает отрицательную сумму', function () {
    Price::fromMajor(-1);
})->throws(InvalidArgumentException::class);

it('запрещает неподдерживаемую валюту', function () {
    Price::fromMinor(100, 'EUR');
})->throws(InvalidArgumentException::class);
