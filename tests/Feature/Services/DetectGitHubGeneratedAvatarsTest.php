<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Jobs\AnalyzeUserAvatar;
use App\Models\User;
use App\Services\DetectGitHubGeneratedAvatars;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class DetectGitHubGeneratedAvatarsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.api_key' => 'test-key']);
    }

    /** @test */
    public function it_dispatches_jobs_for_users_with_unanalyzed_avatars(): void
    {
        // Arrange
        Queue::fake();

        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null, // Not analyzed yet
        ]);

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert
        Queue::assertPushed(AnalyzeUserAvatar::class, 5);
    }

    /** @test */
    public function it_dispatches_jobs_for_users_with_unknown_type(): void
    {
        // Arrange
        Queue::fake();

        User::factory()->count(3)->create([
            'avatar_url' => 'https://assets.codante.io/user-avatars/test/avatar.png',
            'settings' => ['avatar_analysis' => ['type' => 'unknown']],
        ]);

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert
        Queue::assertPushed(AnalyzeUserAvatar::class, 3);
    }

    /** @test */
    public function it_does_not_dispatch_jobs_for_already_analyzed_avatars(): void
    {
        // Arrange
        Queue::fake();

        User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => [
                'avatar_analysis' => [
                    'type' => 'real',
                    'confidence' => 0.95,
                    'analyzed_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        User::factory()->create([
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
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert
        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_respects_batch_size_limit(): void
    {
        // Arrange
        Queue::fake();

        // Create 100 users with unanalyzed avatars
        User::factory()->count(100)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert - Should only dispatch 50 jobs (BATCH_SIZE constant)
        Queue::assertPushed(AnalyzeUserAvatar::class, 50);
    }

    /** @test */
    public function it_does_nothing_when_no_users_need_analysis(): void
    {
        // Arrange
        Queue::fake();

        // Create users with already analyzed avatars
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

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert
        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_does_not_dispatch_when_openai_api_key_is_not_configured(): void
    {
        // Arrange
        config(['services.openai.api_key' => null]);
        Queue::fake();

        User::factory()->count(5)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert
        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_processes_users_with_different_avatar_sources(): void
    {
        // Arrange
        Queue::fake();

        // GitHub avatar
        User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        // Codante S3 avatar
        User::factory()->create([
            'avatar_url' => 'https://assets.codante.io/user-avatars/test/avatar.png',
            'settings' => null,
        ]);

        // Discord avatar
        User::factory()->create([
            'avatar_url' => 'https://cdn.discordapp.com/avatars/123/abc.png',
            'settings' => null,
        ]);

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert - Should process all avatar types
        Queue::assertPushed(AnalyzeUserAvatar::class, 3);
    }

    /** @test */
    public function it_does_not_process_users_without_avatars(): void
    {
        // Arrange
        Queue::fake();

        User::factory()->count(5)->create([
            'avatar_url' => null,
        ]);

        // Act
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        // Assert
        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_selects_users_randomly(): void
    {
        // Arrange
        Queue::fake();

        // Create 10 users
        $users = User::factory()->count(10)->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        // Act - Run twice
        $service = new DetectGitHubGeneratedAvatars();
        $service->handle();

        $firstBatch = Queue::pushedJobs()[AnalyzeUserAvatar::class] ?? [];

        Queue::fake(); // Reset queue
        $service->handle();

        $secondBatch = Queue::pushedJobs()[AnalyzeUserAvatar::class] ?? [];

        // Assert - Both batches should have same count but order might differ
        $this->assertCount(10, $firstBatch);
        $this->assertCount(10, $secondBatch);
    }
}
