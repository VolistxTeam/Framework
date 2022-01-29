<?php

namespace Database\Factories;

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
            'key' => substr($key, 0, 32),
            'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
            'secret_salt' => $salt,
            'permissions' => array('*'),
            'whitelist_range' => array('127.0.0.0'),
            'created_at' => Carbon::now(),
            'activated_at' =>Carbon::now()
        ];
    }
}
