<?php

namespace App\Models;

use App\Support\Casts\PriceCast;
use App\Support\Traits\Models\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'slug',
        'title',
        'price',
        'on_home_page',
        'sorting',
        'brand_id',
    ];

    protected $casts = [
        'price' => PriceCast::class,
    ];

    // Scopes

    public function scopeHomePage(Builder $query)
    {
        $query->where('on_home_page', true)
            ->orderBy('sorting')
            ->limit(8);
    }

    // Relations

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    // Functions

    public function imagePreview(): string
    {
        return $this->images()->where('type', 'preview')->first()?->path ?? Image::PRODUCT_IMAGE_DEFAULT_PREVIEW;
    }
}
