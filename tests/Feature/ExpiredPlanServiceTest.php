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

    /** 
     * @test
     * @group subscription
     */
    public function expired_active_subscriptions_are_marked_as_expired_and_users_downgraded()
    {
        // Arrange
        $user = User::factory()->create(['is_pro' => true]);
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'starts_at' => Carbon::now()->subMonth(),
            'ends_at' => Carbon::now()->subDay(),
            'status' => 'active',
        ]);

        // Act
        ExpiredPlanService::handle();

        // Assert
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'expired',
        ]);
        $this->assertFalse($user->fresh()->is_pro);
    }

    /** 
     * @test
     * @group subscription
     */
    public function users_with_inactive_subscriptions_are_downgraded()
    {
        // Arrange
        $user = User::factory()->create(['is_pro' => true]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'starts_at' => Carbon::now()->subMonth(),
            'status' => 'inactive',
        ]);

        // Act
        ExpiredPlanService::handle();

        // Assert
        $this->assertFalse($user->fresh()->is_pro);
    }

    /** 
     * @test
     * @group subscription
     */
    public function active_subscriptions_not_expired_remain_unchanged()
    {
        // Arrange
        $user = User::factory()->create(['is_pro' => true]);
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'starts_at' => Carbon::now()->subMonth(),
            'ends_at' => Carbon::now()->addDays(5),
            'status' => 'active',
        ]);

        // Act
        ExpiredPlanService::handle();

        // Assert
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'active',
        ]);
        $this->assertTrue($user->fresh()->is_pro);
    }
}
