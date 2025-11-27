<?php

namespace App\Models;

use App\Domains\Order\States\Payment\PaymentState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;

class Payment extends Model
{
    use HasUuids;
    use HasStates;

    protected $fillable = [
        'order_id',
        'payment_id',
        'payment_gateway',
        'meta',
        'state',
    ];

    protected $casts = [
        'meta' => 'collection',
        'state' => PaymentState::class,
    ];

    public function uniqueIds(): array
    {
        return ['payment_id'];
    }

    // Отношения

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }
}
