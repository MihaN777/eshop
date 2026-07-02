<?php

namespace App\Models;

use App\Observers\ImageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[ObservedBy([ImageObserver::class])]
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

    /**
     * Существование файла с кешированием — чтобы не делать проверку состояния ФС на каждый рендер.
     * Инвалидируется в ImageObserver.
     */
    public function exists(): bool
    {
        if (!$this->path) {
            return false;
        }

        return Cache::remember(
            self::existsCacheKey($this->path),
            now()->addDay(),
            fn() => Storage::exists($this->path)
        );
    }

    public static function existsCacheKey(string $path): string
    {
        return 'image_exists_' . md5($path);
    }

    public static function forgetExistsCache(string $path): void
    {
        Cache::forget(self::existsCacheKey($path));
    }
}
