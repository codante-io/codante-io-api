<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;

class CommentController extends Controller
{
    public function show($slug, $githubUser)
    {
        $challengeUser = ChallengeUser::where([
            "challenge_id" => Challenge::where("slug", $slug)->firstOrFail()
                ->id,
            "user_id" => User::where("github_user", $githubUser)->firstOrFail()
                ->id,
        ])->firstOrFail();

        $comments = $challengeUser->commentable()->get();

        return CommentResource::collection($comments);
    }
}
