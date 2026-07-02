<?php

namespace App\Models;

use App\Observers\ProductObserver;
use App\Support\Casts\PriceCast;
use App\Support\Traits\Models\HasSlug;
use App\Support\Traits\Models\WithFilters;
use App\Support\Traits\Models\WithSorting;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use HasSlug;
    use WithSorting;
    use WithFilters;

    protected $fillable = [
        'slug',
        'title',
        'price',
        'quantity',
        'text',
        'json_properties',
        'on_home_page',
        'sorting',
        'brand_id',
    ];

    protected $casts = [
        'price' => PriceCast::class,
        'json_properties' => 'array',
    ];

    // Scopes

    public function scopeHomePage(Builder $query): Builder
    {
        return $query->where('on_home_page', true)
            ->orderBy('sorting')
            ->limit(8);
    }

    // Отношения

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'product_property', 'product_id', 'property_id')
            ->withPivot('value');
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(OptionValue::class, 'option_value_product', 'product_id', 'option_value_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function previewImage(): HasOne
    {
        return $this->hasOne(Image::class)->where('type', Image::IMAGE_TYPE_PREVIEW);
    }

    // Функции модели

    public function deleteWithRelations(): bool
    {
        $enableTransaction = DB::transactionLevel() > 0 ? false : true;

        if ($enableTransaction) DB::beginTransaction();

        try {
            $images = $this->images;
            foreach ($images as $image) $image->delete();

            $this->delete();
        } catch (Throwable) {
            if ($enableTransaction) DB::rollBack();
            return false;
        }

        if ($enableTransaction) DB::commit();

        return true;
    }

    public function storagePreviewImage(): string
    {
        $path = $this->previewImage?->path;

        return (!$path || !Storage::exists($path)) ? Image::PRODUCT_IMAGE_DEFAULT_PREVIEW : '/storage/' . trim($path, '/\\');
    }
}
