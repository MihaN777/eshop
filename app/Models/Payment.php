<?php

namespace App\Models;

use App\Domains\Order\Enums\PaymentStatuses;
use App\Support\Casts\PriceCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    // use HasUuids;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'provider',
        'status',
        'amount',
        'payment_url',
        'meta',
        'expire_at',
    ];

    protected $casts = [
        'amount' => PriceCast::class,
        'meta' => 'array',
        'expire_at' => 'datetime',
    ];

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => PaymentStatuses::from($value)->createState($this)
        );
    }

    // public function uniqueIds(): array
    // {
    //     return ['uuid'];
    // }

    // Отношения

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'transaction_id', 'transaction_id');
    }
}
