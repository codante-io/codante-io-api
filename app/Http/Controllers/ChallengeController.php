<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChallengeResource;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use GrahamCampbell\GitHub\Facades\GitHub;

class ChallengeController extends Controller
{
    public function index()
    {
        return ChallengeResource::collection(
            Challenge::query()
                ->where("status", "published")
                ->orWhere("status", "soon")
                ->with("workshop")
                ->with("workshop.lessons")
                ->withCount("users")
                ->with("tags")
                ->get()
        );
    }

    public function show($slug)
    {
        $challenge = Challenge::where("slug", $slug)
            ->where("status", "published")
            ->with("workshop")
            ->with("workshop.lessons")
            ->withCount("users")
            ->with("tags")
            ->firstOrFail();

        $cacheKey = "challenge_" . $challenge->slug;
        $cacheTime = 60 * 60; // 1 hour
        $repoInfo = cache()->remember($cacheKey, $cacheTime, function () use (
            $challenge
        ) {
            try {
                $repoInfo = GitHub::repo()->show(
                    "codante-io",
                    $challenge->slug
                );
            } catch (\Exception $e) {
                $repoInfo = [
                    "stargazers_count" => 0,
                    "forks_count" => 0,
                ];
            }

            return [
                "stargazers_count" => $repoInfo["stargazers_count"],
                "forks_count" => $repoInfo["forks_count"],
            ];
        });

        # add stars and forks to the challenges
        if ($repoInfo) {
            $challenge->stars = $repoInfo["stargazers_count"];
            $challenge->forks = $repoInfo["forks_count"];
        }

        return response()->json(["data" => $challenge]);
    }

    public function join(Request $request, $slug)
    {
        if (!$request->user()) {
            return response()->json(["error" => "You are not logged in"], 403);
        }
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $challenge->users()->syncWithoutDetaching($request->user()->id);

        return response()->json(["ok" => true], 200);
    }

    public function userJoined(Request $request, $slug)
    {
        $challenge = Challenge::where("slug", $slug)->firstOrFail();

        $challengeUser = $challenge
            ->users()
            ->where("user_id", $request->user()->id)
            ->firstOrFail();
        return $challengeUser;
    }

    public function updateChallengeUser(Request $request, $slug)
    {
        //only the user who joined the challenge can update their own data
        $challengeUser = $this->userJoined($request, $slug);

        if (!$challengeUser) {
            return response()->json(
                ["error" => "You did not join this challenge"],
                403
            );
        }

        $challenge = Challenge::where("slug", $slug)->firstOrFail();

        $validated = $request->validate([
            "completed" => "nullable|boolean",
            "joined_discord" => "nullable|boolean",
            "fork_url" => "nullable|url",
        ]);

        $challenge
            ->users()
            ->updateExistingPivot($request->user()->id, $validated);

        return response()->json(["ok" => true], 200);
    }

    public function getChallengeParticipantsBanner(Request $request, $slug)
    {
        $challenge = Challenge::where("slug", $slug)->firstOrFail();
        $participantsCount = $challenge->users()->count();
        $participantsAvatars = $challenge
            ->users()
            ->get()
            ->map(function ($user) {
                return $user->avatar_url;
            })
            ->take(20);
        return [
            "count" => $participantsCount,
            "avatars" => $participantsAvatars,
        ];
    }
}
