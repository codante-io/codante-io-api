<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;

class BlogPostController extends Controller
{
    public function index()
    {
        return BlogPostResource::collection(
            BlogPost::where('status', 'published')
                ->where('type', 'blog')
                ->with('tags')
                ->with('reactions')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function show(string $slug)
    {
        return new BlogPostResource(
            BlogPost::where('slug', $slug)
                ->where('type', 'blog')
                ->where(function ($query) {
                    $query
                        ->where('status', 'published')
                        ->orWhere('status', 'unlisted');
                })
                ->with('tags')
                ->firstOrFail()
        );
    }

    public function showPage(string $slug)
    {
        return new BlogPostResource(
            BlogPost::where('slug', $slug)
                ->where('type', 'page')
                ->where(function ($query) {
                    $query
                        ->where('status', 'published')
                        ->orWhere('status', 'unlisted');
                })
                ->with('tags')
                ->firstOrFail()
        );
    }
}
