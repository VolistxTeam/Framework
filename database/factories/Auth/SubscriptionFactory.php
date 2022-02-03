<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomNumber(),
            'plan_activated_at' => Carbon::now(),
            'plan_expires_at' => Carbon::now()->addHours($this->faker->numberBetween(24, 720))
        ];
    }
}
