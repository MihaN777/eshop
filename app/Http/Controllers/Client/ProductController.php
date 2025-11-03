<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\ViewModels\Client\ProductViewModel;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function __invoke(Product $product): View
    {
        return view('client.product', new ProductViewModel($product));
    }
}
