<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\ViewModels\Client\CatalogViewModel;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
    public function __invoke(?Category $category): View
    {
        return view('client.catalog.catalog', new CatalogViewModel($category));
    }
}
