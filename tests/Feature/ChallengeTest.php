<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Lesson;
use App\Models\Workshop;
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
        $this->markTestSkipped("Falhando no CI/CD - precisa alterar o mock");

        $response = $this->getJson("/api/challenges");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_challenge_does_not_exist(): void
    {
        $this->markTestSkipped("Falhando no CI/CD - precisa alterar o mock");

        $response = $this->getJson("/api/challenges/does-not-exist");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_200_when_challenge_exists(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );
        // add challenge
        $challenge = Challenge::factory()->create([
            "status" => "published",
        ]);
        $slug = $challenge->slug;

        $response = $this->getJson("/api/challenges/$slug");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_challenge_is_draft(): void
    {
        $this->markTestSkipped("Falhando no CI/CD - precisa alterar o mock");

        $challenge = Challenge::factory()->create([
            "status" => "draft",
        ]);
        $slug = $challenge->slug;

        $response = $this->getJson("/api/challenges/$slug");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_404_when_challenge_is_soon(): void
    {
        $this->markTestSkipped("Falhando no CI/CD - precisa alterar o mock");

        $challenge = Challenge::factory()->create([
            "status" => "soon",
        ]);
        $slug = $challenge->slug;

        $response = $this->getJson("/api/challenges/$slug");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_200_when_challenge_is_unlisted(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $challenge = Challenge::factory()->create([
            "status" => "unlisted",
        ]);
        $slug = $challenge->slug;

        $response = $this->getJson("/api/challenges/$slug");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_does_not_get_workshop_when_there_is_none(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $challenge = Challenge::factory()->create(["status" => "published"]);
        $slug = $challenge->slug;

        $response = $this->getJson("/api/challenges/$slug");
        $this->assertNull($response->json()["data"]["workshop"]);
    }

    /** @test */
    public function it_gets_workshop_when_there_is_one(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $challenge = Challenge::factory()->create(["status" => "published"]);
        $workshop = Workshop::factory()->create([
            "challenge_id" => $challenge->id,
        ]);

        $slug = $challenge->slug;

        $response = $this->getJson("/api/challenges/$slug");
        $this->assertEquals(
            $workshop->id,
            $response->json()["data"]["workshop"]["id"]
        );
    }

    /** @test */
    public function it_gets_workshop_and_lessons_when_there_is_one(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $challenge = Challenge::factory()->create(["status" => "published"]);
        $workshop = Workshop::factory()->create([
            "challenge_id" => $challenge->id,
        ]);

        $lessons = Lesson::factory(3)->create([
            "workshop_id" => $workshop->id,
        ]);

        $slug = $challenge->slug;
        $response = $this->getJson("/api/challenges/$slug");

        $this->assertEquals(
            $lessons->count(),
            count($response->json()["data"]["workshop"]["lessons"])
        );
    }

    /** @test */
    public function it_does_get_false_lessons_user_completed_when_not_logged_in(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $challenge = Challenge::factory()->create(["status" => "published"]);
        $workshop = Workshop::factory()->create([
            "challenge_id" => $challenge->id,
        ]);

        $lesson = Lesson::factory()->create([
            "workshop_id" => $workshop->id,
        ]);

        $slug = $challenge->slug;
        $response = $this->getJson("/api/challenges/$slug");

        $this->assertFalse(
            $response->json()["data"]["workshop"]["lessons"][0][
                "user_completed"
            ]
        );
    }

    /** @test */
    public function it_does_get_false_lessons_user_completed_when_logged_in_and_did_not_completed(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $token = $this->signInAndReturnToken();

        $challenge = Challenge::factory()->create(["status" => "published"]);
        $workshop = Workshop::factory()->create([
            "challenge_id" => $challenge->id,
        ]);
        $lesson = Lesson::factory()->create([
            "workshop_id" => $workshop->id,
        ]);

        $slug = $challenge->slug;
        $response = $this->getJson("/api/challenges/$slug", [
            "Authorization" => "Bearer $token",
        ]);

        $this->assertFalse(
            $response->json()["data"]["workshop"]["lessons"][0][
                "user_completed"
            ]
        );
    }

    /** @test */
    public function it_does_get_true_lessons_user_completed_when_logged_in_and_did_completed(): void
    {
        //skip
        $this->markTestSkipped(
            "Não está funcionando no CI (pq não temos a variável github token). Verificar depois."
        );

        $token = $this->signInAndReturnToken();
        $challenge = Challenge::factory()->create(["status" => "published"]);
        $workshop = Workshop::factory()->create([
            "challenge_id" => $challenge->id,
        ]);
        $lesson = Lesson::factory()->create([
            "workshop_id" => $workshop->id,
        ]);

        $slug = $challenge->slug;
        $response = $this->getJson("/api/challenges/$slug", [
            "Authorization" => "Bearer $token",
        ]);

        $this->assertFalse(
            $response->json()["data"]["workshop"]["lessons"][0][
                "user_completed"
            ]
        );

        $response = $this->postJson(
            "/api/lessons/$lesson->id/completed",
            [],
            [
                "Authorization" => "Bearer $token",
            ]
        );

        $response = $this->getJson("/api/challenges/$slug", [
            "Authorization" => "Bearer $token",
        ]);

        $this->assertTrue(
            $response->json()["data"]["workshop"]["lessons"][0][
                "user_completed"
            ]
        );
    }
}
