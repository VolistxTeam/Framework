<?php

namespace App\Console\Commands;

use App\Models\AccessKeys;
use Illuminate\Console\Command;
use RandomLib\Factory;
use SecurityLib\Strength;

class AccessKeyCommand extends Command
{
    protected $signature = "access-key:generate";

    protected $description = "Create an access key to the system.";

    public function handle()
    {
        $factory = new Factory;
        $generator = $factory->getGenerator(new Strength(Strength::HIGH));

        $generatedKey = $generator->generateString(100, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

        AccessKeys::query()->create(array(
            'token' => $generatedKey,
            'permissions' => empty($permissionLists) ? array('*') : json_decode($permissionLists),
            'whitelist_range' => empty($whitelistRange) ? array() : json_decode($whitelistRange)
        ));

        $this->info('Your access key is created: ' . $generatedKey);
    }
}
