<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eshop:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing command';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
//        DB::beginTransaction();
//
//        $image = Image::query()->where('id', 1)->first();
//        $image->delete();
//
//        $product = Product::query()->where('id', 3)->first();
//        $product->deleteWithRelations();
//
//        DB::commit();

        Category::create([
            'title' => 'Test Category',
            'on_home_page' => true,
        ]);

        return self::SUCCESS;
    }
}
