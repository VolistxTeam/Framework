<?php

namespace Database\Seeders;

use App\Models\PersonalToken;
use App\Models\Plan;
use App\Models\Subscription;
use Faker\Provider\Person;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = Plan::factory()->count(3)->create();
        $subs = Subscription::factory()->for($plans[0])->count(50)->create();
        $tokens = PersonalToken::factory()->for($subs[0])->count(50)->create();
    }
}
