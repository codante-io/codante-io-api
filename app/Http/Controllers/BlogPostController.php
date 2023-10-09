<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index()
    {
        return BlogPostResource::collection(
            BlogPost::where("status", "published")
                ->where("type", "blog")
                ->with("tags")
                ->with("reactions")
                ->get()
        );
    }

    public function show(string $slug)
    {
        return new BlogPostResource(
            BlogPost::where("slug", $slug)
                ->where("type", "blog")
                ->where(function ($query) {
                    $query
                        ->where("status", "published")
                        ->orWhere("status", "unlisted");
                })
                ->with("tags")
                ->firstOrFail()
        );
    }

    public function showPage(string $slug)
    {
        return new BlogPostResource(
            BlogPost::where("slug", $slug)
                ->where("type", "page")
                ->where(function ($query) {
                    $query
                        ->where("status", "published")
                        ->orWhere("status", "unlisted");
                })
                ->with("tags")
                ->firstOrFail()
        );
    }
}
