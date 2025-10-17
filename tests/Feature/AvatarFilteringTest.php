<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class AvatarFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** @test */
    public function it_prioritizes_real_avatars_over_generated_ones(): void
    {
        // Arrange - Create users with different avatar types
        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->count(20)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/654321?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'generated',
                    'confidence' => 0.98,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert - All returned avatars should be real (only 5 exist)
        $this->assertCount(5, $avatars);
    }

    /** @test */
    public function it_returns_16_real_avatars_when_enough_exist(): void
    {
        // Arrange - Create 20 users with real avatars
        User::factory()->count(20)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert
        $this->assertCount(16, $avatars);
    }

    /** @test */
    public function it_supplements_with_unanalyzed_users_when_not_enough_real_avatars(): void
    {
        // Arrange
        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->count(15)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/654321?v=4',
            'settings' => null, // Unanalyzed
        ]);

        User::factory()->count(10)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/999999?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'generated',
                    'confidence' => 0.98,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert - Should return 16 total (5 real + 11 unanalyzed)
        $this->assertCount(16, $avatars);
    }

    /** @test */
    public function it_excludes_generated_avatars_from_supplemental_users(): void
    {
        // Arrange
        User::factory()->count(3)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->count(50)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/999999?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'generated',
                    'confidence' => 0.98,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert - Should only return 3 (only real avatars, no generated ones)
        $this->assertCount(3, $avatars);
    }

    /** @test */
    public function it_handles_users_without_avatars(): void
    {
        // Arrange
        User::factory()->count(10)->create([
            'avatar_url' => null,
        ]);

        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert - Should only return users with avatars
        $this->assertCount(5, $avatars);
    }

    /** @test */
    public function it_includes_users_with_unknown_avatar_type(): void
    {
        // Arrange
        User::factory()->count(10)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'unknown',
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert - Unknown types should be included in supplemental users
        $this->assertCount(10, $avatars);
    }

    /** @test */
    public function it_caches_avatar_section_results(): void
    {
        // Arrange
        User::factory()->count(20)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act - First request
        $response1 = $this->get('/api/home');
        $avatars1 = $response1->json('avatar_section.avatars');

        // Create new users (should not appear due to cache)
        User::factory()->count(10)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/999999?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Second request (cached)
        $response2 = $this->get('/api/home');
        $avatars2 = $response2->json('avatar_section.avatars');

        // Assert - Should return same results due to cache
        $this->assertEquals($avatars1, $avatars2);

        // Clear cache and request again
        Cache::flush();
        $response3 = $this->get('/api/home');
        $avatars3 = $response3->json('avatar_section.avatars');

        // Assert - Should still return 16 but potentially different users
        $this->assertCount(16, $avatars3);
    }

    /** @test */
    public function it_returns_correct_user_count_regardless_of_avatar_analysis(): void
    {
        // Arrange
        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->count(10)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/654321?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'generated',
                    'confidence' => 0.98,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->count(15)->create([
            'avatar_url' => null,
        ]);

        // Act
        $response = $this->get('/api/home');
        $userCount = $response->json('avatar_section.user_count');

        // Assert - Should count all users regardless of avatar type
        $this->assertEquals(30, $userCount);
    }

    /** @test */
    public function it_includes_unsupported_avatar_types_in_supplemental(): void
    {
        // Arrange
        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->count(15)->create([
            'avatar_url' => 'https://assets.codante.io/user-avatars/test/avatar.avif',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'unsupported',
                    'confidence' => 1.0,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        // Act
        $response = $this->get('/api/home');
        $avatars = $response->json('avatar_section.avatars');

        // Assert - Should include unsupported types in supplemental
        $this->assertCount(16, $avatars);
    }
}
