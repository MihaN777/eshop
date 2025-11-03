<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'storage_id',
        'user_id',
    ];

    // Отношения

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
