<?php

namespace App\Models;

use App\Observers\ImageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//#[ObservedBy([ImageObserver::class])]
class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;

    public const IMAGE_TYPE_DEFAULT = 'default';
    public const IMAGE_TYPE_PREVIEW = 'preview';
    public const PRODUCT_IMAGE_DEFAULT_PREVIEW = '/images/defaults/product_preview.jpg';

    protected $fillable = [
        'type',
        'path',
        'product_id',
    ];

    // Отношения

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Функции модели

    public function storageImage(): string
    {
        return '/storage/' . trim($this->path, '/\\');
    }
}
