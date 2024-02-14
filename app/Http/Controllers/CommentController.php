<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Notifications\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create(Request $request)
    {
        Auth::shouldUse("sanctum");

        $request->validate([
            "commentable_type" => "required|in:ChallengeUser,Lesson",
            "commentable_id" => "required",
            "comment" => "required|string",
            "replying_to" => "sometimes|nullable",
        ]);

        $user = Auth::user();

        $commentableClass = Comment::validateCommentable(
            $request->commentable_type
        );

        $replyingTo = $request->replying_to;

        if ($replyingTo !== null) {
            $replyingTo = Comment::validateReply($request->replying_to);
        }

        // check if the commentable exists
        $commentable = $commentableClass::findOrFail($request->commentable_id);

        $comment = Comment::create([
            "commentable_type" => $commentableClass,
            "commentable_id" => $request->commentable_id,
            "comment" => $request->comment,
            "user_id" => $user->id,
            "replying_to" => $replyingTo,
        ]);

        event(new \App\Events\UserCommented($user, $comment, $commentable));

        return response(new CommentResource($comment), 201);
    }

    public function update(Request $request)
    {
        Auth::shouldUse("sanctum");

        $request->validate([
            "comment_id" => "required",
            "comment" => "required|string",
        ]);

        $user = Auth::user();

        $comment = Comment::findOrFail($request->comment_id);

        if ($comment->user_id !== $user->id) {
            return response()->json(
                [
                    "message" =>
                        "Você não tem permissão para editar esse comentário",
                ],
                403
            );
        }

        $comment->comment = $request->comment;
        $comment->save();

        return new CommentResource($comment);
    }

    public function delete(Request $request)
    {
        Auth::shouldUse("sanctum");

        $request->validate([
            "comment_id" => "required",
        ]);

        $user = Auth::user();

        $comment = Comment::findOrFail($request->comment_id);

        if ($comment->user_id !== $user->id) {
            return response()->json(
                [
                    "message" =>
                        "Você não tem permissão para deletar esse comentário",
                ],
                403
            );
        }

        Comment::where("replying_to", $comment->id)->delete();

        $comment->delete();

        return response()->json(["message" => "Comentário deletado"]);
    }
}
