<?php

namespace Database\Factories;

use App\Models\ChallengeUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "user_id" => User::factory(),
            "comment" => fake()->sentence(),
            "replying_to" => null,
            "commentable_type" => "App\Models\ChallengeUser",
            "commentable_id" => ChallengeUser::factory(),
        ];
    }
}
