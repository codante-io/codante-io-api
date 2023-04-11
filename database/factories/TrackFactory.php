<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'short_description' => fake()->paragraphs(1, true),
            'description' => fake()->paragraphs(4, true),
            'image_url' => fake()->imageUrl(640, 480, 'Avatar', true),
            'slug' => fake()->slug(4),
            'status' => fake()->randomElement(['draft', 'published', 'soon', 'archived']),
            'difficulty' => fake()->numberBetween(1, 3),
            'duration_in_minutes' => fake()->numberBetween(60, 300),
        ];
    }
}
