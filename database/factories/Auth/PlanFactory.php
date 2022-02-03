<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        $n = $this->faker->numberBetween(1,50000000);
        return [
            'name' => "plan$n",
            'description' => $this->faker->text(),
            'data' => array('requests' => $this->faker->numberBetween(100, 5000)),
            'created_at' => Carbon::now()
        ];
    }
}
