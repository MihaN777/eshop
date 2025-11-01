<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OptionValue extends Model
{
    /** @use HasFactory<\Database\Factories\OptionValueFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'option_id',
    ];

    // Отношения

    public function option(): belongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'option_value_product', 'option_value_id', 'product_id');
    }
}