<?php

namespace Database\Factories;

use App\Models\Plan;
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
        return [
            'name' => $this->faker->randomElement(['basic','standard','pro','ultimate']),
            'description' => $this->faker->text(),
            'data'=>  array('requests'=> $this->faker->numberBetween(100,5000)),
            'created_at' => Carbon::now()
        ];
    }
}
