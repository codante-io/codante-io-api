<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeResource;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

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
        if (!$request->user()) {
            return response()->json(['error' => 'You are not logged in'], 403);
        }
        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challenge->users()->sync($request->user()->id);

        return response()->json(['ok' => true], 200);
    }

    public function userJoined(Request $request, $slug)
    {
        $token = PersonalAccessToken::findToken($request->bearerToken());

        if (!$token?->tokenable) {
            return response()->json(['error' => 'You are not logged in'], 403);
        }

        $challenge = Challenge::where('slug', $slug)->firstOrFail();

        $challengeUser = $challenge->users()->where('user_id', $token->tokenable->id)->firstOrFail();
        return $challengeUser;
    }

    public function updateChallengeUser(Request $request, $slug)
    {
        //only the user who joined the challenge can update their own data
        $challengeUser = $this->userJoined($request, $slug);

        if (!$challengeUser) {
            return response()->json(['error' => 'You did not join this challenge'], 403);
        }

        $challenge = Challenge::where('slug', $slug)->firstOrFail();
        $challenge->users()->updateExistingPivot($request->user()->id, $request->all());

        return response()->json(['ok' => true], 200);
    }
}
