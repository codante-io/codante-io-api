<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Http\Request;

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
                ->get()
        );
    }

    public function show($slug)
    {
        return new TrackResource(
            Track::query()
                ->where("slug", $slug)
                ->with("tags")
                ->with("trackSections")
                ->firstOrFail()
        );
    }
}
