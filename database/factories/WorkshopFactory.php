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

    //  $table->id();
    //  $table->string('name');
    //  $table->text('description')->nullable();
    //  $table->string('imageUrl')->nullable();
    //  $table->string('slug');
    //  $table->boolean('isPublished')->default(false);
    //  $table->integer('difficulty')->default(1);
    //  $table->integer('duration_in_minutes')->nullable();
    //  $table->foreignId('instructor_id')->nullable()->references('id')->on('instructors');
    //  $table->timestamps();

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'short_description' => fake()->paragraphs(1, true),
            'description' => fake()->paragraphs(4, true),
            'slug' => fake()->slug(4),
            'imageUrl' => fake()->imageUrl(640, 480, 'Avatar', true),
            'isPublished' => fake()->boolean(),
            'difficulty' => fake()->numberBetween(1, 3),
            'duration_in_minutes' => fake()->numberBetween(30, 120),
            'instructor_id' => Instructor::factory(),
        ];
    }
}
