<?php
namespace App\Console\Commands;

use App\Models\AccessKeys;
use Illuminate\Console\Command;
use RandomLib\Factory;

class AccessKeyCommand extends Command
{
    protected $signature = "access-key:create";

    protected $description = "Create an access key to the system.";

    public function handle()
    {
        $factory = new Factory;
        $generator = $factory->getMediumStrengthGenerator();

        $generatedKey = $generator->generateString(32);

        AccessKeys::create(array(
            'token' => $generatedKey,
            'permissions'  => json_encode(array('*'))
        ));

        $this->info('Your access key is created: ' . $generatedKey);
    }
}
