<?php

namespace Database\Factories;

use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => fake()->words(3, true),
            "content" => fake()->paragraphs(4, true),
            "instructor_id" => Instructor::factory(),
            "short_description" => fake()->paragraph(1, true),
            "slug" => fake()->slug(4),
            "image_url" => fake()->imageUrl(640, 480),
            "status" => fake()->randomElement([
                "draft",
                "published",
                "unlisted",
            ]),
        ];
    }
}
