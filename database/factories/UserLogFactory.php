<?php

namespace Database\Factories;

use App\Models\AdminLog;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'url' => $this->faker->url(),
            'request_method'=> $this->faker->randomElement(["POST","GET","PUT","DELETE","PATCH"]),
            'request_body'=> json_encode("body"),
            'request_header'=>json_encode([
                'content-type'=>"application/json"
            ]),
            'ip'=>$this->faker->ipv4(),
            'response_code' => $this->faker->randomElement(["201","204","400","401","403"]),
            'response_body' => json_encode("response_body")
        ];
    }
}
