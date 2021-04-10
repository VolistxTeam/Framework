<?php
namespace App\Console\Commands;

use App\Models\Logs;
use DateTime;
use Illuminate\Console\Command;

class DeleteLogsCommand extends Command
{
    protected $signature = "logs:purge";

    protected $description = "Delete logs created 1 year ago from now.";

    public function handle()
    {
        $date = new DateTime;
        $date->modify('-1 years');
        $formatted = $date->format('Y-m-d H:i:s');
        Logs::where('created_at', '<=', $formatted)->delete();

        $this->info('Log purge is completed.');
    }
}
