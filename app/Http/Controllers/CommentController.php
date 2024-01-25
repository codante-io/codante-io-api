<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Challenge;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create(Request $request)
    {
        Auth::shouldUse("sanctum");

        $request->validate([
            "commentable_type" => "required|in:ChallengeUser",
            "commentable_id" => "required|string",
            "comment" => "required|string",
            "replying_to" => "sometimes|nullable|string",
        ]);

        $user = Auth::user();

        $commentableClass = Comment::validateCommentable(
            $request->commentable_type
        );

        if ($request->replying_to !== null) {
            // validate if the reply has 1 level or less of nesting
            Comment::validateReply($request->replying_to);
        }

        $comment = Comment::createComment(
            $user,
            $commentableClass,
            $request->commentable_id,
            $request->comment,
            $request->replying_to
        );
        return new CommentResource($comment);
    }

    // public function reply(Request $request)
    // {
    //     Auth::shouldUse("sanctum");

    //     $this->validateReply($request);

    //     $user = Auth::user();

    //     $commentableClass = Comment::validateCommentable(
    //         $request->commentable_type
    //     );

    //     $comment = Comment::createComment(
    //         $user,
    //         $commentableClass,
    //         $request->commentable_id,
    //         $request->comment,
    //         $request->replying_to
    //     );
    //     return new CommentResource($comment);
    // }
}
