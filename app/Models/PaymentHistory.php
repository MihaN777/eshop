<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = [
        'transaction_id',
        'provider',
        'method',
        'payload',
    ];

    protected $casts = [
        'payload' => 'collection',
    ];
}
