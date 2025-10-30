<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class CatalogController extends Controller
{
    public function __invoke(?Category $category): View
    {
        // TODO включить sort в запрос с фильтрами
        // $uri = request()->getRequestUri();
        // $queryStr = substr($uri, strpos($uri, '?'));

        $categories = Category::query()
            ->select(['id', 'slug', 'title'])
            ->has('products')
            ->get();

        $products = Product::query()
            ->select(['id', 'slug', 'title', 'price'])
            ->when(request('s'), function (Builder $q) {
                return $q->whereFullText(['title', 'text'], request('s'));
            })
            ->when($category->exists, function (Builder $q) use ($category) {
                return $q->whereRelation(
                    'categories',
                    'categories.id',
                    '=',
                    $category->id
                );
            })
            ->filtered()
            ->sorted()
            ->paginate(6);

        return view('client.catalog.index', compact(
            'category',
            'categories',
            'products',
        ));
    }
}
