<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan; // Added import for Plan
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            "user_id" => User::factory(),
            "plan_id" => Plan::factory(), // Use Plan factory to ensure valid plan_id
            "status" => "active",
            "starts_at" => Carbon::now(), // Default start date
            "ends_at" => Carbon::now()->addMonth(),
            "acquisition_type" => "paid",
            // ...other fields...
        ];
    }
}
