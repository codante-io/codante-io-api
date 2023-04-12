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
                ->with('workshops')
                ->with('challenges')
                ->with('tags')
                ->get()
        );
    }

    public function show($slug)
    {
        return new TrackResource(
            Track::where('slug', $slug)
                ->with('workshops')
                ->with('challenges')
                ->with('tags')
                ->firstOrFail()
        );
    }
}
