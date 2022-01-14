<?php

namespace Database\Factories;

use App\Models\AccessToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccessToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'whitelist_range' => array(),
            'permissions' => array(),
        ];
    }
}