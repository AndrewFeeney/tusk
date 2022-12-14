<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'public_id' => (string) random_int(0, PHP_INT_MAX),
            'instance_id' => null,
            'user_id' => User::factory(),
            'body' => $this->faker->paragraph(),
        ];
    }
}
