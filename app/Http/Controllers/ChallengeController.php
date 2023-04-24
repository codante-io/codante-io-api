<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeResource;
use App\Models\Challenge;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index()
    {
        return ChallengeResource::collection(
            Challenge::query()
                ->with('workshop')
                ->with('workshop.lessons')
                ->with('tags')
                ->get()
        );
    }

    public function show($slug)
    {
        return new ChallengeResource(
            Challenge::where('slug', $slug)
                ->where('status', 'published')
                ->with('workshop')
                ->with('workshop.lessons')
                ->with('tags')
                ->firstOrFail()
        );
    }
}
