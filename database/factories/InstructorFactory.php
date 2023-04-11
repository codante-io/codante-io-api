<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Track>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName() . ' ' . fake()->lastName(),
            'company' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'bio' => fake()->paragraphs(2, true),
            'avatar_url' => fake()->imageUrl(),
            'slug' => fake()->slug(4),
        ];
    }
}
