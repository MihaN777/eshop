<?php

namespace App\Models;

use App\Observers\BrandObserver;
use App\Support\Traits\Models\HasSlug;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([BrandObserver::class])]
class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'slug',
        'title',
        'image',
        'on_home_page',
        'sorting',
    ];

    public function scopeHomePage(Builder $query)
    {
        $query->where('on_home_page', true)
            ->orderBy('sorting')
            ->limit(6);
    }

    // Отношения

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Функции модели

    public function storageImage(): string
    {
        return '/storage/' . trim($this->image, '/\\');
    }
}
