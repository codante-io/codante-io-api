<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
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
            'description' => fake()->paragraphs(2, true),
            'content' => fake()->paragraphs(10, true),
            'duration_in_seconds' => fake()->numberBetween(60, 3600),
            'video_url' => 'https://player.vimeo.com/video/112836958',
            'slug' => fake()->slug(4),
        ];
    }
}
