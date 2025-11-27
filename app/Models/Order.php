<?php

namespace App\Models;

use App\Domains\Order\Enums\OrderStatuses;
use App\Support\Casts\PriceCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'delivery_type_id',
        'payment_method_id',
        'amount',
        'status',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected $casts = [
        'amount' => PriceCast::class,
    ];

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => OrderStatuses::from($value)->createState($this)
        );
    }

    // Отношения

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function orderCustomer(): HasOne
    {
        return $this->hasOne(OrderCustomer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
