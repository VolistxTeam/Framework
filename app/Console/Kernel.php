<?php

namespace App\Console;

use Illuminate\Console\KeyGenerateCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Spatie\ResponseCache\Commands\ClearCommand;
use Mlntn\Console\Commands\Serve;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ExampleCommand::class,
        Commands\AccessKeyCommand::class,
        Commands\DeleteLogsCommand::class,
        ClearCommand::class,
        KeyGenerateCommand::class,
        Serve::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('geoip:update')->daily();
        $schedule->command('logs:purge')->daily();
    }
}
