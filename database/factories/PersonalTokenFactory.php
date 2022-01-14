<?php

namespace Database\Factories;

use App\Models\AccessToken;
use App\Models\PersonalToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PersonalTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PersonalToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $key = Str::random(64);
        $salt = Str::random(16);

        return [
            'user_id' => $this->faker->numberBetween(1,50),
            'key' => substr($key, 0, 32),
            'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
            'secret_salt' => $salt,
            'max_count' => $this->faker->numberBetween(500,5000),
            'permissions' => array("*"),
            'whitelist_range' => array("127.0.0.1"),
            'activated_at' => Carbon::now(),
            'expires_at' =>  Carbon::now()->addHours(720)
        ];
    }
}