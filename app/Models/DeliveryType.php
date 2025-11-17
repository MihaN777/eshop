<?php

namespace App\Models;

use App\Support\Casts\PriceCast;
use Illuminate\Database\Eloquent\Model;

class DeliveryType extends Model
{
    protected $fillable = [
        'title',
        'price',
        'with_address',
    ];

    protected $casts = [
        'price' => PriceCast::class,
    ];
}
