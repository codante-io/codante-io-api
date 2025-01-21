<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User; // Added import for Plan
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(), // Use Plan factory to ensure valid plan_id
            'status' => 'active',
            'starts_at' => Carbon::now(), // Default start date
            'ends_at' => Carbon::now()->addMonth(),
            'acquisition_type' => 'paid',
            // ...other fields...
        ];
    }
}
