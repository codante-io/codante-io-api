<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrackResource;
use App\Models\Track;

class TrackController extends Controller
{
    public function index()
    {
        return TrackResource::collection(
            Track::query()
                ->where("status", "published")
                ->orWhere("status", "soon")
                ->with("workshops")
                ->with("challenges")
                ->with("tags")
                ->orderBy("position", "asc")
                ->get()
        );
    }

    public function show($slug)
    {
        return new TrackResource(
            Track::query()
                ->where("slug", $slug)
                ->with([
                    'workshops.lessons',
                    'workshops.tags',
                    'workshops.instructor',
                    'challenges.users',
                    'challenges.tags',
                    'challenges.workshop.instructor',
                ])
                ->firstOrFail()
        );
    }
}
