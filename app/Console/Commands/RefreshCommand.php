<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RefreshCommand extends Command
{
    protected $signature = 'eshop:refresh';
    protected $description = 'Refresh DB and test data';

    public function handle(): int
    {
        if (app()->isProduction()) return self::FAILURE;

        if (Storage::disk('public')->exists('images/brands'))
            Storage::disk('public')->deleteDirectory('images/brands');
        if (Storage::disk('public')->exists('images/products'))
            Storage::disk('public')->deleteDirectory('images/products');

        Storage::disk('public')->makeDirectory('images/brands');
        Storage::disk('public')->makeDirectory('images/products');

        $this->call('migrate:fresh', ['--seed' => true]);

        return self::SUCCESS;
    }
}
