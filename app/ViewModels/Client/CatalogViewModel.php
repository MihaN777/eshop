<?php

namespace App\ViewModels\Client;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\ViewModels\ViewModel;

class CatalogViewModel extends ViewModel
{
    public function __construct(
        public Category $category
    )
    {
    }

    public function categories(): Collection|array
    {
        return Category::query()
            ->select(['id', 'slug', 'title'])
            ->has('products')
            ->get();
    }

    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->select(['id', 'slug', 'title', 'price', 'json_properties'])
            ->when(request('s'), function (Builder $q) {
                return $q->whereFullText(['title', 'text'], request('s'));
            })
            ->when($this->category->exists, function (Builder $q) {
                return $q->whereRelation(
                    'categories',
                    'categories.id',
                    '=',
                    $this->category->id
                );
            })
            ->filtered()
            ->sorted()
            ->paginate(6);
    }
}
