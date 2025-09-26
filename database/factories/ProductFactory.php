<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(2, true),
            'price' => $this->faker->numberBetween(1000, 10000),
            'brand_id' => Brand::query()->inRandomOrder()->value('id'),

            'image' => '/storage/app/public/images/products/' . $this->faker->file(
                    base_path('/tests/Fixtures/images/products'),
                    storage_path('/app/public/images/products'),
                    false),
        ];
    }
}
