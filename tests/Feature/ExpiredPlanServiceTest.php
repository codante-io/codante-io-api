<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Services\ExpiredPlanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiredPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    // Test to ensure that active subscriptions that have expired are marked as expired
    // and the associated user is downgraded from pro.
    /** @test */
    public function test_handle_expires_active_subscriptions()
    {
        $user = User::factory()->create(["is_pro" => true]);
        $subscription = Subscription::factory()->create([
            "user_id" => $user->id,
            "starts_at" => Carbon::now()->subMonth(),
            "ends_at" => Carbon::now()->subDay(),
            "status" => "active",
        ]);

        ExpiredPlanService::handle();

        $this->assertDatabaseHas("subscriptions", [
            "id" => $subscription->id,
            "status" => "expired",
        ]);

        $this->assertEquals($user->fresh()->is_pro, 0);
    }

    // Test to ensure that users with non-active subscriptions are downgraded from pro.
    public function test_handle_downgrades_outlier_subscriptions()
    {
        $user = User::factory()->create(["is_pro" => true]);
        $subscription = Subscription::factory()->create([
            "user_id" => $user->id,
            "starts_at" => Carbon::now()->subMonth(),
            "status" => "inactive",
        ]);

        ExpiredPlanService::handle();

        $this->assertEquals(0, $user->fresh()->is_pro);
    }
}
