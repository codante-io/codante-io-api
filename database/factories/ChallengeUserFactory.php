<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeUser>
 */
class ChallengeUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'user_id' => User::factory(),
            'completed' => fake()->boolean(),
            'fork_url' => fake()->url(),
            'joined_discord' => fake()->boolean(),
            'submission_url' => fake()->url(),
            'submission_image_url' => fake()->imageUrl(640, 480),
            'is_solution' => fake()->boolean(),
            'featured' => fake()->randomElement(['landing', null, 'new']),
        ];
    }
}
