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
                ->with("tags")
                ->with("reactions")
                ->get()
        );
    }

    public function show(string $slug)
    {
        return new BlogPostResource(
            BlogPost::where("slug", $slug)
                ->with("tags")
                ->firstOrFail()
        );
    }
}
