<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = [
        'transaction_id',
        'description',
        'provider',
        'method',
        'payload',
    ];

    protected $casts = [
        'payload' => 'collection',
    ];
}
