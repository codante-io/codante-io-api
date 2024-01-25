<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_cannot_comment_if_not_logged_in(): void
    {
        $response = $this->postJson("/api/comments");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_comment_if_no_parameters(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("commentable_type");
        $response->assertJsonValidationErrors("commentable_id");
        $response->assertJsonValidationErrors("comment");
    }

    /** @test */
    public function it_cannot_comment_if_no_commentable_id(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_type" => "ChallengeUser",
                "comment" => "Very good!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("commentable_id");
    }

    /** @test */
    public function it_cannot_comment_if_invalid_commentable_id(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_type" => "ChallengeUser",
                "comment" => "Very good!",
                "commentable_id" => "1",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(404);
    }

    /** @test */
    public function it_cannot_comment_if_no_commentable_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_id" => "1",
                "comment" => "Very good!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("commentable_type");
    }

    /** @test */
    public function it_cannot_comment_if_invalid_commentable_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_id" => "1",
                "commentable_type" => "InvalidModel",
                "comment" => "Very good!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors("commentable_type");
        $response->assertJsonFragment([
            "commentable_type" => [
                "O campo commentable type selecionado é inválido.",
            ],
        ]);
    }

    /** @test */
    public function it_cannot_comment_if_model_is_not_commentable()
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_id" => "1",
                "commentable_type" => "User",
                "comment" => "Very good!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors("commentable_type");

        // message should be Model is not commentable
        $response->assertJsonFragment([
            "commentable_type" => [
                "O campo commentable type selecionado é inválido.",
            ],
        ]);
    }

    /** @test */
    public function it_cannot_comment_if_no_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_id" => "1",
                "commentable_type" => "App\Models\BlogPost",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors("comment");
    }

    /** @test */
    public function it_can_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $challengeUser = \App\Models\ChallengeUser::factory()->create();

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_id" => $challengeUser->id,
                "commentable_type" => "ChallengeUser",
                "comment" => "Very good!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(201);

        // assert comment
        $this->assertDatabaseHas("comments", [
            "commentable_id" => $challengeUser->id,
            "commentable_type" => "App\Models\ChallengeUser",
            "comment" => "Very good!",
        ]);
    }

    /** @test */
    public function it_can_not_update_a_comment_if_user_is_not_the_owner_of_the_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $comment = \App\Models\Comment::factory()->create();

        $response = $this->putJson(
            "/api/comments",
            [
                "comment_id" => $comment->id,
                "comment" => "Nice! I like it!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(403);
    }

    public function it_can_update_a_comment(): void
    {
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth["user"];
        $token = $auth["token"];

        $comment = \App\Models\Comment::factory()->create([
            "user_id" => $user->id,
        ]);

        $response = $this->putJson(
            "/api/comments",
            [
                "comment_id" => $comment->id,
                "comment" => "Nice! I like it!",
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(200);

        // assert comment
        $this->assertDatabaseHas("comments", [
            "id" => $comment->id,
            "comment" => "Nice! I like it!",
        ]);
    }

    /** @test */
    public function it_can_not_delete_a_comment_if_user_is_not_the_owner_of_the_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $comment = \App\Models\Comment::factory()->create();

        $response = $this->deleteJson(
            "/api/comments",
            [
                "comment_id" => $comment->id,
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_a_comment(): void
    {
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth["user"];
        $token = $auth["token"];

        $comment = \App\Models\Comment::factory()->create([
            "user_id" => $user->id,
        ]);

        $response = $this->deleteJson(
            "/api/comments",
            [
                "comment_id" => $comment->id,
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(200);

        // assert comment
        $this->assertSoftDeleted("comments", [
            "id" => $comment->id,
        ]);
    }

    /** @test */
    public function it_can_reply_to_a_comment(): void
    {
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth["user"];
        $token = $auth["token"];

        $challengeUser = \App\Models\ChallengeUser::factory()->create();

        $comment = \App\Models\Comment::factory()->create([
            "user_id" => $user->id,
            "commentable_id" => $challengeUser->id,
        ]);

        $response = $this->postJson(
            "/api/comments",
            [
                "commentable_id" => $challengeUser->id,
                "commentable_type" => "ChallengeUser",
                "comment" => "Hey! This is a response.",
                "replying_to" => $comment->id,
            ],
            [
                "Authorization" => "Bearer $token",
            ]
        );
        $response->assertStatus(201);

        // assert comment
        $this->assertDatabaseHas("comments", [
            "id" => $response->json("id"),
            "commentable_id" => $comment->id,
            "commentable_type" => "App\Models\ChallengeUser",
            "comment" => "Hey! This is a response.",
            "replying_to" => $comment->id,
        ]);
    }
}
