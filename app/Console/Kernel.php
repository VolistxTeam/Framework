<?php

namespace App\Console;

use Illuminate\Console\KeyGenerateCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Mlntn\Console\Commands\Serve;
use Spatie\ResponseCache\Commands\ClearCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AccessKey\GenerateCommand::class,
        Commands\AccessKey\DeleteCommand::class,
        Commands\DeleteLogsCommand::class,
        ClearCommand::class,
        KeyGenerateCommand::class,
        Serve::class,

        // Custom Commands Here
        Commands\ExampleCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('geoip:update')->daily();
        $schedule->command('logs:purge')->daily();
    }
}
