<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function __invoke(Product $product): View
    {
        $product->load(['optionValues.option']);

        $options = $product->optionValues->mapToGroups(function ($item) {
            return [$item->option->title => $item];
        });

        // Просмотренные товары
        $alsoProducts = collect();

        if (session()->has('also')) {
            $also = collect(session()->get('also'))
                ->except($product->id)
                ->reverse()
                ->slice(0, 4);

            if ($also->isNotEmpty()) $alsoProducts = Product::query()->whereIn('id', $also->toArray())->limit(4)->get();
        }

        // Запоминание просмотренных товаров
        session()->put('also.' . $product->id, $product->id);

        return view('client.product', compact(
            'product',
            'options',
            'alsoProducts',
        ));
    }
}
