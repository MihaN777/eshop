<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    // Отношения

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_property', 'property_id', 'product_id')
            ->withPivot('value');
    }
}
