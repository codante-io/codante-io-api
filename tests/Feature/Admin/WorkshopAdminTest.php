<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopAdminTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $regularUser;
    private Workshop $workshop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(["is_admin" => true]);
        $this->regularUser = User::factory()->create(["is_admin" => false]);
        $this->workshop = Workshop::factory()->create();
    }

    public function test_admin_can_edit_workshop()
    {
        $response = $this->actingAs($this->admin, "sanctum")->putJson(
            "/api/custom-admin/workshops/{$this->workshop->id}",
            [
                "video_url" => "https://example.com/new-video",
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure(["message", "workshop"]);

        $this->assertEquals(
            "https://example.com/new-video",
            $this->workshop->fresh()->video_url
        );
    }

    public function test_regular_user_cannot_edit_workshop()
    {
        $response = $this->actingAs($this->regularUser, "sanctum")->putJson(
            "/api/custom-admin/workshops/{$this->workshop->id}",
            [
                "video_url" => "https://example.com/new-video",
            ]
        );

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_edit_workshop()
    {
        $response = $this->putJson(
            "/api/custom-admin/workshops/{$this->workshop->id}",
            [
                "video_url" => "https://example.com/new-video",
            ]
        );

        $response->assertStatus(401);
    }

    public function test_cannot_edit_nonexistent_workshop()
    {
        $response = $this->actingAs($this->admin, "sanctum")->putJson(
            "/api/custom-admin/workshops/9999999",
            [
                "video_url" => "https://example.com/new-video",
            ]
        );

        $response
            ->assertStatus(404)
            ->assertJson(["message" => "Workshop not found"]);
    }

    public function test_validation_fails_without_data()
    {
        $response = $this->actingAs($this->admin, "sanctum")->putJson(
            "/api/custom-admin/workshops/{$this->workshop->id}"
        );

        $response->assertStatus(400);
    }
}
