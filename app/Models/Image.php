<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
