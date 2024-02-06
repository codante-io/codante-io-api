<?php

namespace Tests\Feature;

use Illuminate\Auth\Events\Registered;
use App\Mail\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([Registered::class]);
    }

    protected function tearDown(): void
    {
        \Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function it_cannot_login_with_wrong_credentials(): void
    {
        $response = $this->postJson("/api/login", [
            "email" => "lala@lala.com",
            "password" => "password",
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            "password" => bcrypt("password"),
        ]);

        $response = $this->postJson("/api/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_user_token_after_login(): void
    {
        $user = User::factory()->create([
            "email" => "correct@email.com",
            "password" => bcrypt("password"),
        ]);

        // get user token
        $response = $this->postJson("/api/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        // clear cookies
        $this->assertCount(1, $user->tokens);
        $response->assertJsonStructure(["token"]);
    }

    /** @test */
    public function it_can_logout(): void
    {
        $user = User::factory()->create([
            "password" => bcrypt("password"),
        ]);

        $response = $this->postJson("/api/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        $this->assertCount(1, $user->tokens);

        $token = $response->json()["token"];

        $response = $this->postJson(
            "/api/logout",
            [],
            [
                "Authorization" => "Bearer $token",
            ]
        );

        // there should be no tokens
        $user = User::find($user->id);
        $this->assertCount(0, $user->tokens);
        $response->assertStatus(204);
    }

    /** @test */
    public function it_cannot_logout_without_token(): void
    {
        $response = $this->postJson("/api/logout");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_register_a_user(): void
    {
        $email = "email@teste.com";
        $response = $this->postJson("/api/register", [
            "name" => "John Doe",
            "email" => $email,
            "password" => "password",
            "password_confirmation" => "password",
        ]);
        $response->assertStatus(201);
    }

    /** @test */
    public function it_sends_email_when_register_user(): void
    {
        Mail::fake();

        Mail::assertNothingSent();
        Event::assertNothingDispatched();

        $email = "email@teste.com";

        $response = $this->postJson("/api/register", [
            "name" => "John Doe",
            "email" => $email,
            "password" => "password",
            "password_confirmation" => "password",
        ]);

        // assert event was dispatched

        Event::assertDispatchedTimes(Registered::class, 1);
        // assert email sent
        Mail::assertSent(UserRegistered::class);

        Mail::assertSent(UserRegistered::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        // assert email subject
        Mail::assertSent(UserRegistered::class, function ($mail) {
            return $mail->hasSubject("Bem vindo ao Codante.io!");
        });

        $response->assertStatus(201);
    }

    /** @test */
    public function it_cannot_register_a_user_with_wrong_data(): void
    {
        $response = $this->postJson("/api/register", [
            "name" => "John Doe",
            "email" => "lala@lalalala.com",
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_cannot_register_a_user_with_existing_email(): void
    {
        $email = "lala@lala.com";
        User::factory()->create([
            "email" => $email,
        ]);

        $response = $this->postJson("/api/register", [
            "name" => "John Doe",
            "email" => $email,
            "password" => "password",
            "password_confirmation" => "password",
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_user_data(): void
    {
        $user = User::factory()->create([
            "password" => bcrypt("password"),
        ]);

        $response = $this->postJson("/api/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        $token = $response->json()["token"];

        $response = $this->getJson("/api/user", [
            "Authorization" => "Bearer $token",
        ]);
        $jsonResponse = $response->json();

        $this->assertEquals($user->email, $jsonResponse["email"]);
        $this->assertEquals($user->name, $jsonResponse["name"]);

        $response->assertJsonStructure([
            "id",
            "name",
            "email",
            "github_id",
            "github_user",
            "linkedin_user",
            "discord_user",
            "is_pro",
            "is_admin",
            "settings",
            "avatar" => ["avatar_url", "name", "badge"],
            "created_at",
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_cannot_get_user_data_without_token(): void
    {
        $response = $this->getJson("/api/user");

        $response->assertStatus(401);
    }
}
