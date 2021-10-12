<?php

namespace App\Console\Commands\AccessKey;

use App\Classes\PermissionsCenter;
use Illuminate\Console\Command;

class DeleteCommand extends Command
{
    protected $signature = "access-key:delete {--key=}";

    protected $description = "Delete an access key";

    public function handle()
    {
        $token = $this->option('key');

        if (empty($token)) {
            $this->error('Please specify your access key to delete.');
            return;
        }

        $accessKey = PermissionsCenter::getAdminAuthKey($token);

        if (empty($accessKey)) {
            $this->error('The specified access key is invalid.');
            return;
        }

        $accessKey->delete();

        $this->info('Your access key is deleted: ' . $token);
    }
}
