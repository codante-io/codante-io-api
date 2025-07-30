<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('cache:clear');
        Cache::clear();
        // $this->artisan("db:seed");
    }

    /** @test */
    public function it_get_status_200(): void
    {
        $response = $this->get('/api/home');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_has_the_required_keys(): void
    {
        $response = $this->get('/api/home');

        $response->assertJsonStructure([
            'avatar_section',
            'live_streaming_workshop',
            'featured_workshops',
            'featured_challenges',
            'featured_testimonials',
            'featured_submissions',
            'plan_info',
        ]);
    }

    /** @test */
    public function it_has_correct_user_count_number(): void
    {
        $response = $this->get('/api/home');

        //assert this number is 0
        $this->assertEquals(
            0,
            $response->json()['avatar_section']['user_count']
        );

        Cache::flush();

        // add 5 users
        User::factory()
            ->count(5)
            ->create();

        $response = $this->get('/api/home');
        //assert this number is 5
        $this->assertEquals(
            5,
            $response->json()['avatar_section']['user_count']
        );
    }

    /**
     * @test
     *
     * @group only
     */
    public function it_has_16_avatars(): void
    {
        Cache::flush();

        User::factory()
            ->count(50)
            ->create();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(16, $responseJson['avatar_section']['avatars']);
    }

    /** @test */
    public function it_handles_nonexistent_route()
    {
        $response = $this->get('/api/this-route-does-not-exist');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_has_live_streaming_workshop_when_status_is_streaming(): void
    {
        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertNull($responseJson['live_streaming_workshop']);

        // create a workshop with status streaming
        $workshop = Workshop::factory()->create([
            'status' => 'streaming',
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertEquals(
            $workshop->id,
            $responseJson['live_streaming_workshop']['id']
        );

        $workshop = Workshop::find($workshop->id);
        $workshop->status = 'published';
        $workshop->save();

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertNull($responseJson['live_streaming_workshop']);
    }

    /** @test */
    public function it_does_not_have_live_streaming_workshop_when_status_is_not_streaming(): void
    {
        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertNull($responseJson['live_streaming_workshop']);

        // create a workshop with status streaming
        $workshop = Workshop::factory()->create([
            'status' => 'published',
        ]);
        $workshop = Workshop::factory()->create([
            'status' => 'draft',
        ]);
        $workshop = Workshop::factory()->create([
            'status' => 'archived',
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertNull($responseJson['live_streaming_workshop']);

        $workshop = Workshop::factory()->create([
            'status' => 'streaming',
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertEquals(
            $workshop->id,
            $responseJson['live_streaming_workshop']['id']
        );
    }

    /** @test */
    public function it_has_featured_workshops(): void
    {
        Cache::flush();

        $workshop = Workshop::factory()->create([
            'featured' => null,
            'status' => 'published',
        ]);

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(0, $responseJson['featured_workshops']);

        // create a workshop with status streaming
        $workshop = Workshop::factory()->create([
            'featured' => 'landing',
            'status' => 'published',
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(1, $responseJson['featured_workshops']);
    }

    /** @test */
    public function it_should_not_have_featured_workshop_to_a_draft_workshop()
    {
        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(0, $responseJson['featured_workshops']);

        // create a workshop with status streaming
        $workshop = Workshop::factory()->create([
            'featured' => 'landing',
            'status' => 'draft',
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(0, $responseJson['featured_workshops']);
    }

    /** @test */
    public function it_should_have_featured_testimonials()
    {
        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(0, $responseJson['featured_testimonials']);

        // Create a testimonial
        $testimonial = Testimonial::factory()->create([
            'featured' => null,
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(0, $responseJson['featured_testimonials']);

        Cache::flush();
        // Create a testimonial
        $testimonial = Testimonial::factory()->create([
            'featured' => 'landing',
        ]);

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(1, $responseJson['featured_testimonials']);

        Cache::flush();
        // Create a testimonial
        $testimonial = Testimonial::factory()->create([
            'featured' => 'landing',
        ]);

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(2, $responseJson['featured_testimonials']);

        Cache::flush();
        // TODO: limit testimonials to X

        // Create a testimonial
        //     $testimonial = Testimonial::factory()
        //         ->count(50)
        //         ->create([
        //             "featured" => "landing",
        //         ]);

        //     $response = $this->get("/api/home");
        //     $responseJson = $response->json();
        //     $this->assertCount(10, $responseJson["featured_testimonials"]);
    }

    /** @test */
    public function it_should_have_featured_challenges()
    {
        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();

        $this->assertCount(0, $responseJson['featured_challenges']);

        // Create a challenge
        $challenge = Challenge::factory()->create([
            'featured' => null,
            'status' => 'published',
        ]);

        Cache::flush();

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(0, $responseJson['featured_challenges']);

        Cache::flush();
        // Create a challenge
        $challenge = Challenge::factory()->create([
            'featured' => 'landing',
            'status' => 'published',
        ]);

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(1, $responseJson['featured_challenges']);

        Cache::flush();
        $challenge = Challenge::factory()->create([
            'featured' => 'landing',
            'status' => 'published',
        ]);

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(2, $responseJson['featured_challenges']);

        // vamos criar 3 challenges com featured = landing e status = draft (não devem aparecer)
        // vamos criar 5 challenges com featured = null e status = published (não devem aparecer)
        // vamos criar 1 challenge com featured = landing e status = published (deve aparecer)
        Cache::flush();
        $challenge = Challenge::factory()
            ->count(3)
            ->create([
                'featured' => 'landing',
                'status' => 'draft',
            ]);

        $challenge = Challenge::factory()
            ->count(1)
            ->create([
                'featured' => 'landing',
                'status' => 'published',
            ]);

        $challenge = Challenge::factory()
            ->count(5)
            ->create([
                'featured' => null,
                'status' => 'published',
            ]);

        $response = $this->get('/api/home');
        $responseJson = $response->json();
        $this->assertCount(3, $responseJson['featured_challenges']);

        Cache::flush();
    }
}
