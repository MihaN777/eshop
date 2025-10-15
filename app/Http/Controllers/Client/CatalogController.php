<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories(): View
    {
        $categories = Category::query()->get();

        return view('client.catalog.categories', compact(
            'categories',
        ));
    }

    public function categoryProducts(Category $category): View
    {
        $products = $category->products;

        return view('client.catalog.category-products', compact(
            'category',
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
