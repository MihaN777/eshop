<?php

namespace App\ViewModels\Client;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Spatie\ViewModels\ViewModel;

class ProductViewModel extends ViewModel
{
    public function __construct(
        public Product $product
    )
    {
        $this->product->load(['optionValues.option']);

        // Запоминание просмотренных товаров
        session()->put('also.' . $this->product->id, $this->product->id);
    }

    public function options(): BaseCollection|array
    {
        return $this->product->optionValues->mapToGroups(function ($item) {
            return [$item->option->title => $item];
        });
    }

    public function alsoProducts(): Collection
    {
        // Просмотренные товары
        if (session()->has('also')) {
            $also = collect(session()->get('also'))
                ->except($this->product->id)
                ->reverse()
                ->slice(0, 4);

            if ($also->isNotEmpty()) {
                return Product::query()
                    ->with('previewImage')
                    ->whereIn('id', $also->toArray())
                    ->limit(4)
                    ->get();
            }
        }

        return new Collection;
    }
}
