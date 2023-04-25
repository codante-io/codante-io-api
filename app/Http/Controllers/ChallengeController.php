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
                ->where('status', 'published')
                ->orWhere('status', 'soon')
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

    public function join(Request $request, $slug)
    {
        $challenge = Challenge::where('slug', $slug)->firstOrFail();

        $challenge->users()->attach($request->user()->id);
        dd($challenge->users);
        dd($request->user()->id);
        // return new ChallengeResource(
        //     Challenge::where('slug', $slug)
        //         ->with('workshop')
        //         ->with('workshop.lessons')
        //         ->with('tags')
        //         ->firstOrFail()
        // );
    }
}
