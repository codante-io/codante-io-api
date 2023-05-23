<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkshopResource;
use App\Models\Tag;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WorkshopController extends Controller
{
    public function index()
    {
        return WorkshopResource::collection(
            Workshop::query()
                ->where('is_standalone', true)
                ->with('lessons')
                ->with('instructor')
                ->with('tags')
                ->visible()
                ->get()
        );
    }

    public function show($slug)
    {
        // add cache 5 minutes
        return Cache::remember("workshop_content_$slug", 60 * 5, function () use ($slug) {
            return new WorkshopResource(
                Workshop::where('slug', $slug)
                    ->with('lessons')
                    ->with('instructor')
                    ->with('tags')
                    ->firstOrFail()
            );
        });
    }
}
