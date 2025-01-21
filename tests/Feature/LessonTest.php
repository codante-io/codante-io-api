<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonTest extends TestCase
{
    use RefreshDatabase;

    public function signInAndReturnToken($user = null)
    {
        $user =
            $user ?:
            \App\Models\User::factory()->create([
                'password' => bcrypt('password'),
            ]);
        // $this->actingAs($user);

        // get api key
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json()['token'];

        return $token;
    }

    /** @test */
    public function it_cannot_complete_when_not_logged_in(): void
    {
        $lesson = \App\Models\Lesson::factory()->create();

        $response = $this->postJson("/api/lessons/$lesson->id/completed");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_uncomplete_when_not_logged_in()
    {
        $lesson = \App\Models\Lesson::factory()->create();

        $response = $this->postJson("/api/lessons/$lesson->id/uncompleted");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_complete_lesson(): void
    {
        $lesson = \App\Models\Lesson::factory()->create();

        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $token = $this->signInAndReturnToken($user);

        $this->assertEquals(0, $lesson->users()->count());

        $response = $this->postJson(
            "/api/lessons/$lesson->id/completed",
            [],
            [
                'Authorization' => "Bearer $token",
            ]
        );

        $this->assertEquals(1, $lesson->users()->count());
        $this->assertEquals($user->id, $lesson->users()->first()->id);
        $this->assertEquals($lesson->id, $lesson->id);
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_logged_in_and_completing_an_inexistent_lesson(): void
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $token = $this->signInAndReturnToken($user);

        $response = $this->postJson(
            '/api/lessons/9999/completed',
            [],
            [
                'Authorization' => "Bearer $token",
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_uncomplete_lesson(): void
    {
        $lesson = \App\Models\Lesson::factory()->create();

        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $token = $this->signInAndReturnToken($user);

        $this->assertEquals(0, $lesson->users()->count());

        $response = $this->postJson(
            "/api/lessons/$lesson->id/completed",
            [],
            [
                'Authorization' => "Bearer $token",
            ]
        );

        $this->assertEquals(1, $lesson->users()->count());

        $response = $this->postJson(
            "/api/lessons/$lesson->id/uncompleted",
            [],
            [
                'Authorization' => "Bearer $token",
            ]
        );

        $this->assertEquals(0, $lesson->users()->count());
        $response->assertStatus(200);
    }
}
