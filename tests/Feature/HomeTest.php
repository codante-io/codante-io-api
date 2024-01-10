<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // $this->artisan("db:seed");
    }

    /** @test */
    public function it_get_status_200(): void
    {
        $response = $this->get("/api/home");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_has_the_required_keys(): void
    {
        $response = $this->get("/api/home");

        $response->assertJsonStructure([
            "avatar_section",
            "live_streaming_workshop",
            "featured_workshops",
            "featured_challenges",
            "featured_testimonials",
            "featured_submissions",
            "plan_info",
        ]);
    }
}
