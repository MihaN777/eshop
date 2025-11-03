<?php

namespace App\ViewModels\Client;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\ViewModels\ViewModel;

class HomeViewModel extends ViewModel
{
    public function __construct()
    {
        //
    }

    public function brands(): mixed
    {
        return Cache::rememberForever('home-page.brands', function () {
            return Brand::query()
                ->homePage()
                ->get();
        });
    }

    public function categories(): mixed
    {
        return Cache::rememberForever('home-page.categories', function () {
            return Category::query()
                ->homePage()
                ->get();
        });
    }

    public function products(): Collection
    {
        return Product::query()
            ->homePage()
            ->get();
    }
}
