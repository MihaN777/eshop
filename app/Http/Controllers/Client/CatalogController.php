<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function catalog(?Category $category): View
    {
        $brands = Brand::query()
            ->select(['id', 'title'])
            ->has('products')
            ->get();

        $categories = Category::query()
            ->select(['id', 'slug', 'title'])
            ->has('products')
            ->get();

        $products = Product::query()
            ->select(['id', 'slug', 'title', 'price'])
            ->filtered()
            ->sorted()
            ->paginate(6);

        //        $products = null;
        //        if (!$category->exists) {
        //            $products = Product::query()
        //                ->select(['id', 'slug', 'title', 'price'])
        //                ->filtered()
        //                ->sorted()
        //                ->paginate(6);
        //        } else {
        //            $products = $category->products()
        //                ->select(['id', 'slug', 'title', 'price'])
        //                ->filtered()
        //                ->sorted()
        //                ->paginate(6);
        //        }

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
