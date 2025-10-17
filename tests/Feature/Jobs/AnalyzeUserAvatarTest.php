<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\AnalyzeUserAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use ReflectionClass;
use Tests\TestCase;

final class AnalyzeUserAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.api_key' => 'test-api-key']);
    }

    /** @test */
    public function it_marks_avatar_as_real_when_openai_returns_real(): void
    {
        // Arrange
        $user = User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        // Mock HTTP responses - fake image download and OpenAI response
        Http::fake([
            'https://avatars.githubusercontent.com/u/123456*' => Http::response(file_get_contents(__DIR__ . '/../../fixtures/avatar.png')),
            'https://api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'real',
                                'confidence' => 0.95,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $job = new AnalyzeUserAvatar($user->id, $user->avatar_url);
        $job->handle();

        // Assert
        $user->refresh();
        $this->assertNotNull($user->settings);
        $this->assertEquals('real', $user->settings['avatar_analysis']['type']);
        $this->assertEquals(0.95, $user->settings['avatar_analysis']['confidence']);
        $this->assertNotNull($user->settings['avatar_analysis']['analyzed_at']);
    }

    /** @test */
    public function it_marks_avatar_as_generated_when_openai_returns_generated(): void
    {
        // Arrange
        $user = User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/654321?v=4',
            'settings' => null,
        ]);

        Http::fake([
            'https://avatars.githubusercontent.com/*' => Http::response(
                file_get_contents(__DIR__ . '/../../fixtures/avatar.png')
            ),
            'https://api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'generated',
                                'confidence' => 0.98,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $job = new AnalyzeUserAvatar($user->id, $user->avatar_url);
        $job->handle();

        // Assert
        $user->refresh();
        $this->assertEquals('generated', $user->settings['avatar_analysis']['type']);
        $this->assertEquals(0.98, $user->settings['avatar_analysis']['confidence']);
    }

    /** @test */
    public function it_marks_avatar_as_unsupported_for_avif_format(): void
    {
        // Arrange
        $user = User::factory()->create([
            'avatar_url' => 'https://assets.codante.io/user-avatars/test123/avatar.avif',
            'settings' => null,
        ]);

        Http::fake([
            '*' => Http::response('', 200),
        ]);

        // Act
        $job = new AnalyzeUserAvatar($user->id, $user->avatar_url);
        $job->handle();

        // Assert
        $user->refresh();
        $this->assertEquals('unsupported', $user->settings['avatar_analysis']['type']);
        $this->assertEquals(1.0, $user->settings['avatar_analysis']['confidence']);
    }

    /** @test */
    public function it_can_be_dispatched_to_queue(): void
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
        ]);

        // Act
        AnalyzeUserAvatar::dispatch($user->id, $user->avatar_url);

        // Assert
        Queue::assertPushed(AnalyzeUserAvatar::class, function ($job) use ($user) {
            $reflection = new ReflectionClass($job);

            $userId = $reflection->getProperty('userId');
            $userId->setAccessible(true);
            $avatarUrl = $reflection->getProperty('avatarUrl');
            $avatarUrl->setAccessible(true);

            return $userId->getValue($job) === $user->id
                && $avatarUrl->getValue($job) === $user->avatar_url;
        });
    }

    /** @test */
    public function it_does_not_analyze_when_openai_api_key_is_not_configured(): void
    {
        // Arrange
        config(['services.openai.api_key' => null]);

        $user = User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        // Act
        $job = new AnalyzeUserAvatar($user->id, $user->avatar_url);
        $job->handle();

        // Assert
        $user->refresh();
        $this->assertNull($user->settings);
    }

    /** @test */
    public function it_throws_exception_when_openai_returns_error(): void
    {
        // Arrange
        $user = User::factory()->create([
            'avatar_url' => 'https://avatars.githubusercontent.com/u/123456?v=4',
            'settings' => null,
        ]);

        Http::fake([
            'https://avatars.githubusercontent.com/*' => Http::response(
                file_get_contents(__DIR__ . '/../../fixtures/avatar.png')
            ),
            'https://api.openai.com/*' => Http::response([
                'error' => [
                    'message' => 'Rate limit exceeded',
                ],
            ], 429),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAI API request failed:');

        $job = new AnalyzeUserAvatar($user->id, $user->avatar_url);
        $job->handle();
    }

    /** @test */
    public function it_does_not_update_user_when_user_is_deleted(): void
    {
        // Arrange
        $userId = 99999; // Non-existent user

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'real',
                                'confidence' => 0.95,
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $job = new AnalyzeUserAvatar($userId, 'https://avatars.githubusercontent.com/u/123456?v=4');
        $job->handle();

        // Assert - should not throw exception, just log warning
        $this->assertTrue(true);
    }
}
