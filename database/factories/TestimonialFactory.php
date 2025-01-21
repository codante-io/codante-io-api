<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'body' => $this->faker->text,
            'social_media_link' => $this->faker->url,
            'social_media_nickname' => $this->faker->userName(),
            'avatar_url' => $this->faker->url,
            'company' => $this->faker->company,
            'source' => $this->faker->company,
            'featured' => $this->faker->randomElement([null, 'landing']),
        ];
    }
}
