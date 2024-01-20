<?php

namespace Tests\Feature;

use App\Models\Reaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReactionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_cannot_react_if_not_logged_in(): void
    {
        $response = $this->postJson("/api/reactions");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_react_if_no_reaction_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/reactions",
            [],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("reactable_id");
        $response->assertJsonValidationErrors("reactable_type");
        $response->assertJsonValidationErrors("reaction");
    }

    /** @test */
    public function it_cannot_react_if_no_reactable_id(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_type" => "App\Models\BlogPost",
                "reaction" => "like",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("reactable_id");
    }

    /** @test */
    public function it_cannot_react_if_no_reactable_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_id" => 1,
                "reaction" => "like",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("reactable_type");
    }

    /** @test */
    public function it_cannot_react_if_no_reaction(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_id" => 1,
                "reactable_type" => "App\Models\BlogPost",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("reaction");
    }

    /** @test */
    public function it_cannot_react_if_invalid_reactable_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_id" => 1,
                "reactable_type" => "InvalidModel",
                "reaction" => "like",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors("reactable_type");
        $response->assertJsonFragment([
            "reactable_type" => ["Reactable model does not exist."],
        ]);
    }

    /** @test */
    public function it_cannot_react_if_model_is_not_reactable()
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_id" => 1,
                "reactable_type" => "User",
                "reaction" => "like",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors("reactable_type");

        // message should be Model is not reactable
        $response->assertJsonFragment([
            "reactable_type" => ["Model is not reactable."],
        ]);
    }

    /** @test */
    public function it_can_react(): void
    {
        $token = $this->signInAndReturnToken();

        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_id" => $blogPost->id,
                "reactable_type" => "BlogPost",
                "reaction" => "like",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(201);

        // assert reaction
        $this->assertDatabaseHas("reactions", [
            "reactable_id" => $blogPost->id,
            "reactable_type" => "App\Models\BlogPost",
            "reaction" => "like",
        ]);
    }

    /** @test */
    public function it_can_unreact()
    {
        $user = \App\Models\User::factory()->create([
            "password" => bcrypt("password"),
        ]);

        $token = $this->signInAndReturnToken($user);

        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);

        $reaction = \App\Models\Reaction::factory()->create([
            "reactable_id" => $blogPost->id,
            "reactable_type" => "App\Models\BlogPost",
            "reaction" => "like",
            "user_id" => $user->id,
        ]);

        $reactions = \App\Models\Reaction::all();
        $this->assertCount(1, $reactions);

        $response = $this->postJson(
            "/api/reactions",
            [
                "reactable_id" => $blogPost->id,
                "reactable_type" => "BlogPost",
                "reaction" => "like",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(204);

        $reactions = \App\Models\Reaction::all();
        $this->assertCount(0, $reactions);
    }
}
