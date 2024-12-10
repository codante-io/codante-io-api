<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            "name" => $this->faker->word,
            "price_in_cents" => $this->faker->numberBetween(1000, 10000),
            "duration_in_months" => $this->faker->numberBetween(1, 12),
            "slug" => $this->faker->slug,
            "details" => "{}",
            // ...other fields...
        ];
    }
}
