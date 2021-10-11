<?php

namespace App\Console\Commands;

use App\Models\AccessKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccessKeyCommand extends Command
{
    protected $signature = "access-key:generate";

    protected $description = "Create an access key to the system.";

    public function handle()
    {
        $key = Str::random(64);
        $salt = Str::random(16);

        AccessKeys::query()->create(array(
            'key' => substr($key, 0, 32),
            'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
            'secret_salt' => $salt,
            'permissions' => empty($permissionLists) ? array('*') : json_decode($permissionLists),
            'whitelist_range' => empty($whitelistRange) ? array() : json_decode($whitelistRange)
        ));

        $this->info('Your access key is created: ' . $key);
    }
}
