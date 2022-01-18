<?php

namespace Database\Factories;

use App\Models\Log;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'key' => $this->faker->name(),
            'value' => $this->faker->name(),
            'type' => $this->faker->randomElement(["TYPE 1","TYPE 2","TYPE 3"])
        ];
    }
}
