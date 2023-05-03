<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workshop>
 */
class WorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * 
     * 
     * 
     */


    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'short_description' => fake()->paragraph(2, true),
            'description' => fake()->paragraphs(4, true),
            'image_url' => fake()->imageUrl(640, 480, 'Avatar', true),
            'video_url' => fake()->imageUrl(640, 480, 'Avatar', true),
            'slug' => fake()->slug(4),
            'status' => fake()->randomElement(['draft', 'published', 'soon', 'archived']),
            'is_standalone' => fake()->boolean(),
            'difficulty' => fake()->numberBetween(1, 3),
            'duration_in_minutes' => fake()->numberBetween(60, 300),
            'instructor_id' => Instructor::factory(),
            'featured' => fake()->randomElement(['landing', null, 'new']),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
