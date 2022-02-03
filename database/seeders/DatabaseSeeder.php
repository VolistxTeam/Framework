<?php

namespace Database\Seeders;

use App\Models\Auth\PersonalToken;
use App\Models\Auth\Plan;
use App\Models\Auth\Subscription;
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
