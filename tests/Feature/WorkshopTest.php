<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_200_to_workshop_post_list(): void
    {
        $response = $this->getJson('/api/workshops');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_workshop_does_not_exist(): void
    {
        $response = $this->getJson('/api/workshops/does-not-exist');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_200_when_workshop_exists(): void
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        $response = $this->getJson("/api/workshops/$slug");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_workshop_is_draft()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'draft',
        ]);
        $slug = $workshop->slug;

        $response = $this->getJson("/api/workshops/$slug");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_200_when_workshop_is_unlisted()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'unlisted',
        ]);
        $slug = $workshop->slug;

        $response = $this->getJson("/api/workshops/$slug");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_not_shows_workshop_in_workshops_list_if_unlisted()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'unlisted',
        ]);
        $slug = $workshop->slug;

        $response = $this->getJson('/api/workshops/');
        $jsonResponse = $response->json();
        $this->assertEquals(0, count($jsonResponse['data']));
    }

    /** @test */
    public function it_shows_workshop_in_workshops_list_if_published()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/workshops/');
        $jsonResponse = $response->json();
        $this->assertEquals(1, count($jsonResponse['data']));
    }

    public function it_shows_workshop_in_workshops_list_if_soon()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'soon',
        ]);

        $response = $this->getJson('/api/workshops/');
        $jsonResponse = $response->json();
        $this->assertEquals(1, count($jsonResponse['data']));
    }

    /** @test */
    public function it_has_the_correct_shape()
    {
        $this->markTestSkipped('This test is skipped.');

        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        $response = $this->getJson("/api/workshops/$slug");
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'content',
                'image_url',
                'short_description',
                'slug',
                'status',
                'created_at',
                'reactions',
                'instructor',
                'tags',
            ],
        ]);
    }

    /** @test */
    public function it_has_the_correct_instructor()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        $response = $this->getJson("/api/workshops/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            $workshop->instructor->name,
            $jsonResponse['data']['instructor']['name']
        );
    }

    /** @test */
    public function it_has_the_correct_tags()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        // add tag
        $tag = \App\Models\Tag::factory()->create();
        $workshop->tags()->attach($tag);

        $response = $this->getJson("/api/workshops/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            $workshop->tags->count(),
            count($jsonResponse['data']['tags'])
        );

        $this->assertEquals(
            $tag->name,
            $jsonResponse['data']['tags'][0]['name']
        );
    }

    /** @test */
    public function it_has_lessons()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        // add lesson
        $lesson = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
        ]);

        // dd($lesson);
        $response = $this->getJson("/api/workshops/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            $workshop->lessons->count(),
            count($jsonResponse['data']['lessons']),
            1
        );

        // dd($workshop->lessons->count());
        $this->assertEquals(
            $lesson->name,
            $jsonResponse['data']['lessons'][0]['name']
        );
    }

    /** @test */
    public function not_logged_user_cannot_see_protected_lesson_from_workshop()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        // add lesson
        $lesson = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'all',
        ]);
        $lesson1 = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'pro',
        ]);
        $lesson2 = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'logged_in',
        ]);

        $response = $this->getJson("/api/workshops/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(true, $jsonResponse['data']['lessons'][0]['user_can_view']);
        $this->assertEquals(
            false,
            $jsonResponse['data']['lessons'][1]['user_can_view'],
            $jsonResponse['data']['lessons'][2]['user_can_view']
        );

        // lesson in workshop has this shape
        $this->assertArrayHasKey('id', $jsonResponse['data']['lessons'][0]);
        $this->assertArrayHasKey('name', $jsonResponse['data']['lessons'][0]);
        $this->assertArrayHasKey('slug', $jsonResponse['data']['lessons'][0]);
        $this->assertArrayHasKey('url', $jsonResponse['data']['lessons'][0]);
        $this->assertArrayHasKey(
            'thumbnail_url',
            $jsonResponse['data']['lessons'][0]
        );
        $this->assertArrayHasKey(
            'user_completed',
            $jsonResponse['data']['lessons'][0]
        );
        $this->assertArrayHasKey(
            'duration_in_seconds',
            $jsonResponse['data']['lessons'][0]
        );
        $this->assertArrayHasKey('user', $jsonResponse['data']['lessons'][0]);
    }

    /** @test */
    public function logged_in_user_can_see_logged_in_lessons_opened()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        // add lesson
        $lesson = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'all',
        ]);
        $lesson1 = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'pro',
        ]);
        $lesson2 = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'logged_in',
        ]);

        $user = \App\Models\User::factory()->create();
        $token = $this->signInAndReturnToken($user);

        $response = $this->getJson("/api/workshops/$slug", [
            'Authorization' => "Bearer $token",
        ]);
        $jsonResponse = $response->json();

        $this->assertEquals(true, $jsonResponse['data']['lessons'][0]['user_can_view']);
        $this->assertEquals(false, $jsonResponse['data']['lessons'][1]['user_can_view']);
        $this->assertEquals(true, $jsonResponse['data']['lessons'][2]['user_can_view']);
    }

    /** @test */
    public function logged_in_user_can_see_pro_lessons_opened()
    {
        // add workshop
        $workshop = \App\Models\Workshop::factory()->create([
            'status' => 'published',
        ]);
        $slug = $workshop->slug;

        // add lesson
        $lesson = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'all',
        ]);
        $lesson1 = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'pro',
        ]);
        $lesson2 = \App\Models\Lesson::factory()->create([
            'lessonable_id' => $workshop->id,
            'lessonable_type' => get_class($workshop),
            'available_to' => 'logged_in',
        ]);

        $user = \App\Models\User::factory()->create(['is_pro' => true]);
        $token = $this->signInAndReturnToken($user);

        $response = $this->getJson("/api/workshops/$slug", [
            'Authorization' => "Bearer $token",
        ]);
        $jsonResponse = $response->json();

        $this->assertEquals(true, $jsonResponse['data']['lessons'][0]['user_can_view']);
        $this->assertEquals(true, $jsonResponse['data']['lessons'][1]['user_can_view']);
        $this->assertEquals(true, $jsonResponse['data']['lessons'][2]['user_can_view']);
    }
}
