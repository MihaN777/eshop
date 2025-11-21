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

        Storage::deleteDirectory('images/brands');
        Storage::deleteDirectory('images/products');

        $this->info('Start refresh');

        $this->call('migrate:fresh', ['--seed' => true]);
        $this->call('queue:work', ['--stop-when-empty' => true]);

        $this->info('Refresh done');

        return self::SUCCESS;
    }
}
