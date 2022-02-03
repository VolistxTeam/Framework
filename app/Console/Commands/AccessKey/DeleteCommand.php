<?php

namespace App\Console\Commands\AccessKey;

use App\Classes\Auth\PermissionsCenter;
use App\Repositories\Auth\AccessTokenRepository;
use Illuminate\Console\Command;
use function Symfony\Component\Translation\t;

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

        $repo = new AccessTokenRepository();
        $accessToken = $repo->AuthAccessToken($token);

        if (!$accessToken) {
            $this->error('The specified access key is invalid.');
            return;
        }

        $accessToken->delete();

        $this->info('Your access key is deleted: ' . $token);
    }
}
