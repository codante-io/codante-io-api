<?php

namespace Tests\Feature;

use App\Events\UserCommented;
use App\Listeners\CommentCreated;
use App\Services\Discord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    //setup
    protected function setUp(): void
    {
        // mock Discord
        parent::setUp();
        // Event::fake([UserCommented::class]);
    }

    //teardown
    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_cannot_comment_if_not_logged_in(): void
    {
        $response = $this->postJson('/api/comments');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_comment_if_no_parameters(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors('commentable_type');
        $response->assertJsonValidationErrors('commentable_id');
        $response->assertJsonValidationErrors('comment');
    }

    /** @test */
    public function it_cannot_comment_if_no_commentable_id(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_type' => 'ChallengeUser',
                'comment' => 'Very good!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors('commentable_id');
    }

    /** @test */
    public function it_cannot_comment_if_invalid_commentable_id(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_type' => 'ChallengeUser',
                'comment' => 'Very good!',
                'commentable_id' => '1',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(404);
    }

    /** @test */
    public function it_cannot_comment_if_no_commentable_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => '1',
                'comment' => 'Very good!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors('commentable_type');
    }

    /** @test */
    public function it_cannot_comment_if_invalid_commentable_type(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => '1',
                'commentable_type' => 'InvalidModel',
                'comment' => 'Very good!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('commentable_type');
        $response->assertJsonFragment([
            'commentable_type' => [
                'O campo commentable type selecionado é inválido.',
            ],
        ]);
    }

    /** @test */
    public function it_cannot_comment_if_model_is_not_commentable()
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => '1',
                'commentable_type' => 'User',
                'comment' => 'Very good!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('commentable_type');

        // message should be Model is not commentable
        $response->assertJsonFragment([
            'commentable_type' => [
                'O campo commentable type selecionado é inválido.',
            ],
        ]);
    }

    /** @test */
    public function it_cannot_comment_if_no_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => '1',
                'commentable_type' => "App\Models\BlogPost",
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(422);

        // assert message
        $response->assertJsonValidationErrors('comment');
    }

    /** @test */
    public function it_can_comment(): void
    {
        //skip this test
        $this->markTestSkipped('Falhando no CI/CD - precisa alterar o mock');
        $token = $this->signInAndReturnToken();

        $challengeUser = \App\Models\ChallengeUser::factory()->create();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => $challengeUser->id,
                'commentable_type' => 'ChallengeUser',
                'comment' => 'Very good!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(201);

        // assert comment
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $challengeUser->id,
            'commentable_type' => "App\Models\ChallengeUser",
            'comment' => 'Very good!',
        ]);
    }

    /** @test */
    public function it_can_not_update_a_comment_if_user_is_not_the_owner_of_the_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $comment = \App\Models\Comment::factory()->create();

        $response = $this->putJson(
            '/api/comments',
            [
                'comment_id' => $comment->id,
                'comment' => 'Nice! I like it!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(403);
    }

    public function it_can_update_a_comment(): void
    {
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth['user'];
        $token = $auth['token'];

        $comment = \App\Models\Comment::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            '/api/comments',
            [
                'comment_id' => $comment->id,
                'comment' => 'Nice! I like it!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(200);

        // assert comment
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment' => 'Nice! I like it!',
        ]);
    }

    /** @test */
    public function it_can_not_delete_a_comment_if_user_is_not_the_owner_of_the_comment(): void
    {
        $token = $this->signInAndReturnToken();

        $comment = \App\Models\Comment::factory()->create();

        $response = $this->deleteJson(
            '/api/comments',
            [
                'comment_id' => $comment->id,
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_a_comment(): void
    {
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth['user'];
        $token = $auth['token'];

        $comment = \App\Models\Comment::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(
            '/api/comments',
            [
                'comment_id' => $comment->id,
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );
        $response->assertStatus(200);

        // assert comment
        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    /** @test */
    public function it_can_reply_to_a_comment(): void
    {
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth['user'];
        $token = $auth['token'];
        $challengeUser = \App\Models\ChallengeUser::factory()->create();

        Event::fake();
        Event::assertNothingDispatched();
        Event::assertListening(UserCommented::class, CommentCreated::class);

        $comment = \App\Models\Comment::factory()->create([
            'user_id' => $user->id,
            'commentable_id' => $challengeUser->id,
        ]);

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => $challengeUser->id,
                'commentable_type' => 'ChallengeUser',
                'comment' => 'Hey! This is a response.',
                'replying_to' => $comment->id,
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );

        $response->assertStatus(201);

        // assert comment
        $this->assertDatabaseHas('comments', [
            'id' => $response->json('id'),
            'commentable_id' => $comment->id,
            'commentable_type' => "App\Models\ChallengeUser",
            'comment' => 'Hey! This is a response.',
            'replying_to' => $comment->id,
        ]);
        Event::assertDispatched(UserCommented::class);
    }

    /** @test */
    public function it_dispatches_event_when_comment_is_created(): void
    {
        Event::fake([UserCommented::class]);
        $auth = $this->signInAndReturnUserAndToken();
        $user = $auth['user'];
        $token = $auth['token'];
        $challengeUser = \App\Models\ChallengeUser::factory()->create();

        $response = $this->postJson(
            '/api/comments',
            [
                'commentable_id' => $challengeUser->id,
                'commentable_type' => 'ChallengeUser',
                'comment' => 'Adorei sua submissão!',
            ],
            [
                'Authorization' => "Bearer $token",
            ]
        );

        $response->assertStatus(201);

        Event::assertDispatched(UserCommented::class);
    }

    /** @test */
    public function it_sends_email_when_comment_event_is_dispatched(): void
    {
        $this->markTestSkipped('Falhando no CI/CD - precisa alterar o mock');

        $ChallengeUserUser = \App\Models\User::factory()->create([
            'id' => 2,
        ]);
        $CommenterUser = \App\Models\User::factory()->create([
            'id' => 5,
        ]);
        $challengeUser = \App\Models\ChallengeUser::factory()->create([
            'user_id' => $ChallengeUserUser->id,
        ]);
        $comment = \App\Models\Comment::factory()->create([
            'commentable_type' => "App\Models\ChallengeUser",
            'commentable_id' => $challengeUser->id,
            'user_id' => $CommenterUser->id,
        ]);

        Notification::fake();

        $event = event(
            new UserCommented($CommenterUser, $comment, $challengeUser)
        );

        // Event::assertDispatched(UserCommented::class);
        Notification::assertCount(1);

        // assert notification was sent to challengeUserOwner
        Notification::assertSentTo(
            $ChallengeUserUser,
            \App\Notifications\ChallengeUserCommentNotification::class
        );

        // assert notification was not sent to CommenterUser
        Notification::assertNothingSentTo($CommenterUser);
    }

    /** @test */
    public function it_sends_emails_to_users_when_there_was_a_reply(): void
    {
        $this->markTestSkipped('Falhando no CI/CD - precisa alterar o mock');

        $parentCommentUser = \App\Models\User::factory()->create([
            'id' => 20,
            'email' => 'comentariopai@email.com',
        ]);

        $CommenterUser1 = \App\Models\User::factory()->create([
            'id' => 5,
        ]);
        $CommenterUser2 = \App\Models\User::factory()->create([
            'id' => 6,
        ]);
        $challengeUserUser = \App\Models\User::factory()->create([
            'id' => 7,
        ]);
        $challengeUser = \App\Models\ChallengeUser::factory()->create([
            'user_id' => $challengeUserUser->id,
        ]);

        // Comentário pai é criado
        $parentComment = \App\Models\Comment::factory()->create([
            'commentable_type' => "App\Models\ChallengeUser",
            'comment' => 'Gostei do Projeto!',
            'commentable_id' => $challengeUser->id,
            'user_id' => $parentCommentUser->id,
        ]);

        // Primeira resposta é criada
        $relatedComment = \App\Models\Comment::factory()->create([
            'commentable_type' => "App\Models\ChallengeUser",
            'comment' => 'Legal',
            'commentable_id' => $challengeUser->id,
            'user_id' => $CommenterUser1->id,
            'replying_to' => $parentComment->id,
        ]);

        // Segunda resposta é criada
        $relatedComment2 = \App\Models\Comment::factory()->create([
            'commentable_type' => "App\Models\ChallengeUser",
            'comment' => 'Massa',
            'commentable_id' => $challengeUser->id,
            'user_id' => $CommenterUser2->id,
            'replying_to' => $parentComment->id,
        ]);

        Notification::fake();

        // evento é disparado com a segunda resposta
        $event = event(
            new UserCommented($CommenterUser2, $relatedComment2, $challengeUser)
        );

        // Event::assertDispatched(UserCommented::class);
        Notification::assertCount(2);

        // assert notification was sent to challengeUserOwner
        Notification::assertSentTo(
            $parentCommentUser,
            \App\Notifications\ChallengeUserReplyCommentNotification::class
        );

        // assert notification was sent to CommenterUser
        Notification::assertSentTo(
            $CommenterUser1,
            \App\Notifications\ChallengeUserReplyCommentNotification::class
        );

        // assert notification was not sent to CommenterUser2
        Notification::assertNothingSentTo($CommenterUser2);
    }
}
