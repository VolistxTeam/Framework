<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExampleCommand extends Command
{
    protected $signature = 'example:test';

    protected $description = 'Test';

    public function handle()
    {
        $this->info('Test');
    }
}
