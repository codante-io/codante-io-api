<?php

namespace Tests\Feature;

use App\Models\Challenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_200_to_challenge_list(): void
    {
        $response = $this->getJson("/api/challenges");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_challenge_does_not_exist(): void
    {
        $response = $this->getJson("/api/challenges/does-not-exist");
        $response->assertStatus(404);
    }

    // /** @test */
    // public function it_gets_200_when_challenge_exists(): void
    // {
    //     // add challenge
    //     $challenge = Challenge::factory()->create();
    //     $slug = $challenge->slug;

    //     $response = $this->getJson("/api/challenges/$slug");

    //     $response->assertStatus(200);
    //     dd($slug);
    // }
}
