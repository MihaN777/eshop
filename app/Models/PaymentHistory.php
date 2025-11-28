<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentHistory extends Model
{
    protected $fillable = [
        'payment_id',
        'payment_provider',
        'method',
        'payload',
    ];

    protected $casts = [
        'payload' => 'collection',
    ];

    // Отношения

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
