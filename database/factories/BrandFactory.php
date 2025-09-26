<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
            
            'image' => '/storage/app/public/images/brands/' . $this->faker->file(
                    base_path('/tests/Fixtures/images/brands'),
                    storage_path('/app/public/images/brands'),
                    false),
        ];
    }
}
