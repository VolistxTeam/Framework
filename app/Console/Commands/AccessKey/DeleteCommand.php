<?php

namespace App\Console\Commands\AccessKey;

use App\Models\AccessKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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

        $accessKey = AccessKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();

        if (empty($accessKey)) {
            $this->error('The specified access key is invalid.');
            return;
        }

        $accessKey->delete();

        $this->info('Your access key is deleted: ' . $token);
    }
}
