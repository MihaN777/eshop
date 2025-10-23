<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Console\Command;
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
    public function handle()
    {
//        DB::beginTransaction();
//        $image = Image::query()->where('id', 5)->first();
//        $image->delete();
//
//        $product = Product::query()->where('id', 7)->first();
//        $product->delete();
//        DB::commit();

        return self::SUCCESS;
    }
}
