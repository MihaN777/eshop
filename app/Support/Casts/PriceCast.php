<?php

namespace App\Support\Casts;

use App\Support\ValueObjects\Price;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PriceCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Price
    {
        return Price::make($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): float|int
    {
        if (!$value instanceof Price) $value = Price::make($value);

        return $value->raw();
    }
}
