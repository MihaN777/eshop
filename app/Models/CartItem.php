<?php

namespace App\Models;

use App\Support\Casts\PriceCast;
use App\Support\ValueObjects\Price;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'price',
        'quantity',
        'string_option_values',
    ];

    protected $casts = [
        'price' => PriceCast::class,
    ];

    // Отношения

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(OptionValue::class, 'cart_item_option_value', 'cart_item_id', 'option_value_id');
    }

    // Функции модели

    public function amount(): Attribute
    {
        return Attribute::make(
            get: fn() => Price::make(
                $this->price->row() * $this->quantity
            )
        );
    }
}
