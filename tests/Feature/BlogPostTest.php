<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_200_to_blog_post_list(): void
    {
        $response = $this->getJson("/api/blog-posts");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_blog_post_does_not_exist(): void
    {
        $response = $this->getJson("/api/blog-posts/does-not-exist");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_200_when_blog_post_exists(): void
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);
        $slug = $blogPost->slug;

        $response = $this->getJson("/api/blog-posts/$slug");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_gets_404_when_blog_post_is_draft()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "draft",
        ]);
        $slug = $blogPost->slug;

        $response = $this->getJson("/api/blog-posts/$slug");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_gets_200_when_blog_post_is_unlisted()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "unlisted",
        ]);
        $slug = $blogPost->slug;

        $response = $this->getJson("/api/blog-posts/$slug");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_has_the_correct_shape()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);
        $slug = $blogPost->slug;

        $response = $this->getJson("/api/blog-posts/$slug");
        $response->assertJsonStructure([
            "data" => [
                "id",
                "title",
                "content",
                "image_url",
                "short_description",
                "slug",
                "status",
                "created_at",
                "reactions",
                "instructor",
                "tags",
            ],
        ]);
    }

    /** @test */
    public function it_has_the_correct_instructor()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);
        $slug = $blogPost->slug;

        $response = $this->getJson("/api/blog-posts/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            $blogPost->instructor->name,
            $jsonResponse["data"]["instructor"]["name"]
        );
    }

    /** @test */
    public function it_has_the_correct_tags()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);
        $slug = $blogPost->slug;

        // add tag
        $tag = \App\Models\Tag::factory()->create();
        $blogPost->tags()->attach($tag);

        $response = $this->getJson("/api/blog-posts/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            $blogPost->tags->count(),
            count($jsonResponse["data"]["tags"])
        );

        $this->assertEquals(
            $tag->name,
            $jsonResponse["data"]["tags"][0]["name"]
        );
    }

    /** @test */
    public function it_starts_with_zero_reactions()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);
        $slug = $blogPost->slug;

        $response = $this->getJson("/api/blog-posts/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            0,
            count($jsonResponse["data"]["reactions"]["reaction_counts"])
        );
    }

    /** @test */
    public function it_has_the_correct_reactions()
    {
        // add blog post
        $blogPost = \App\Models\BlogPost::factory()->create([
            "status" => "published",
        ]);
        $slug = $blogPost->slug;

        // add reaction
        $reaction = \App\Models\Reaction::factory()->create([
            "reactable_id" => $blogPost->id,
            "reactable_type" => "App\Models\BlogPost",
            "reaction" => "like",
        ]);

        $response = $this->getJson("/api/blog-posts/$slug");
        $jsonResponse = $response->json();

        $this->assertEquals(
            1,
            count($jsonResponse["data"]["reactions"]["reaction_counts"])
        );

        $this->assertEquals(
            "like",
            $jsonResponse["data"]["reactions"]["reaction_counts"][0]["reaction"]
        );
    }
}
