<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => Image::IMAGE_TYPE_PREVIEW,
            'path' => $this->faker->fixturesImage('products', 'images/products'),
            'product_id' => Product::query()->inRandomOrder()->value('id'),
        ];
    }
}
