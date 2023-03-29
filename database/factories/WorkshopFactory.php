<?php

namespace Database\Factories;

use App\Models\Instructor;
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
            'slug' => fake()->slug(4),
            'imageURL' => fake()->imageUrl(640, 480, 'Avatar', true),
            'isPublished' => fake()->boolean(),
            'difficulty' => fake()->numberBetween(1, 3),
            'duration_in_minutes' => fake()->numberBetween(60, 300),
            'instructor_id' => Instructor::factory(),
        ];
    }
}
