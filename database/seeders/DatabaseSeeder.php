<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Brand::factory(5)->create();

        $properties = Property::factory(10)->create();

        Category::factory(5)
            ->has(Product::factory(rand(5, 10))
                ->has(Image::factory(1))
                ->hasAttached($properties, function () {
                    return ['value' => ucfirst(fake()->word())];
                })
            )
            ->create();
    }
}
