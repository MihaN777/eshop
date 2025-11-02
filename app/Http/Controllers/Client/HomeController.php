<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $brands = Cache::rememberForever('home-page.brands', function () {
            return Brand::query()
                ->homePage()
                ->get();
        });

        $categories = Cache::rememberForever('home-page.categories', function () {
            return Category::query()
                ->homePage()
                ->get();
        });

        $products = Product::query()
            ->homePage()
            ->get();

        return view('client.home', compact(
            'brands',
            'categories',
            'products'
        ));
    }
}
