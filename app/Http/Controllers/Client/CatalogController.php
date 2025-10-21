<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CatalogController extends Controller
{
    public function catalog(Category $category): View
    {
        $brands = Brand::all();
        $categories = Category::all();

        $products = new LengthAwarePaginator(new Collection([]), 0, 1);

        if (!$category->exists) {
            $products = Product::query()->paginate(12);
        } else {
            $products = $category->products()->paginate(12);
        }

        return view('client.catalog.index', compact(
            'category',
            'brands',
            'categories',
            'products',
        ));
    }

    public function product(Product $product): View
    {
        return view('client.catalog.product', compact(
            'product',
        ));
    }
}
