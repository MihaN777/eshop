<?php

namespace App\Models;

use App\Domains\Order\States\Payment\PaymentState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

class Payment extends Model
{
    use HasUuids;
    use HasStates;

    protected $fillable = [
        'payment_id',
        'payment_gateway',
        'meta',
    ];

    protected $casts = [
        'meta' => 'collection',
        'state' => PaymentState::class,
    ];

    public function uniqueIds(): array
    {
        return ['payment_id'];
    }
}
