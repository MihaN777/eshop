<?php

namespace Tests\Feature\App\Models;

use App\Models\Image;
use App\Models\Product;
use App\Observers\ImageObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPreviewImageTest extends TestCase
{
    use RefreshDatabase;

    private function makeProductWithPreview(string $path): Product
    {
        $product = Product::factory()->create();

        Image::query()->create([
            'product_id' => $product->id,
            'type' => Image::IMAGE_TYPE_PREVIEW,
            'path' => $path,
        ]);

        return $product->load('previewImage');
    }

    /**
     * Существование файла кешируется: после удаления файла в обход модели превью
     * всё ещё считается существующим (значение из кеша); после сброса — пересчёт.
     */
    public function test_storage_preview_image_caches_existence_check(): void
    {
        Storage::fake();
        Storage::put('products/p.jpg', 'x');

        $product = $this->makeProductWithPreview('products/p.jpg');

        $this->assertSame('/storage/products/p.jpg', $product->storagePreviewImage());

        Storage::delete('products/p.jpg');

        // Значение существования взято из кеша — всё ещё URL из storage.
        $this->assertSame('/storage/products/p.jpg', $product->storagePreviewImage());

        // Сброс кеша -> пересчёт -> файла нет -> дефолт.
        Image::forgetExistsCache('products/p.jpg');
        $this->assertSame(Image::PRODUCT_IMAGE_DEFAULT_PREVIEW, $product->storagePreviewImage());
    }

    /**
     * ImageObserver::deleted сбрасывает кеш существования.
     * Обсёрвер помечен ShouldHandleEventsAfterCommit и под RefreshDatabase не сработает
     * автоматически, поэтому его логику проверяем прямым вызовом.
     */
    public function test_deleting_image_forgets_exists_cache(): void
    {
        Storage::fake();
        Storage::put('products/q.jpg', 'x');

        $image = Image::query()->create([
            'product_id' => Product::factory()->create()->id,
            'type' => Image::IMAGE_TYPE_PREVIEW,
            'path' => 'products/q.jpg',
        ]);

        $image->exists();
        $this->assertTrue(Cache::has(Image::existsCacheKey('products/q.jpg')));

        (new ImageObserver())->deleted($image);

        $this->assertFalse(Cache::has(Image::existsCacheKey('products/q.jpg')));
    }
}
