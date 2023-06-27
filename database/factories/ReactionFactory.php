<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reaction>
 */
class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reaction' => $this->faker->randomElement(['like', 'fire', 'rocket', 'exploding-head']),
            'user_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
