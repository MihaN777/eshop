<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCustomer extends Model
{
    protected $fillable = [
        'order_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'city',
        'address',
    ];

    // Отношения

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
