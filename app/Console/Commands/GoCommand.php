<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:go';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom command app:go';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return 0;
    }
}
