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
            "commentable_type" => "required|in:ChallengeUser",
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

        $comment = Comment::createComment(
            $user,
            $commentableClass,
            $request->commentable_id,
            $request->comment,
            $replyingTo
        );

        new Discord(
            "Um novo comentário foi feito por {$user->name} em {$request->commentable_type} {$request->commentable_id} {replying to - $replyingTo}: {$request->comment}",
            "notificacoes-comentarios"
        );

        // dd($commentableClass, $replyingTo);

        if (
            $commentableClass === "App\Models\ChallengeUser" &&
            $replyingTo === null
        ) {
            $challengeUser = $comment->commentable;
            // dd($challengeUser->user);

            $challengeUser->user->notify(
                new \App\Notifications\ChallengeUserCommentNotification(
                    $comment
                )
            );
        }

        if (
            $commentableClass === "App\Models\ChallengeUser" &&
            $replyingTo !== null
        ) {
            $parentComment = Comment::find($replyingTo);
            $relatedComments = Comment::where(
                "replying_to",
                $replyingTo
            )->get();

            $users = $relatedComments->map(function ($comment) {
                return $comment->user;
            });

            $users->push($parentComment->user);

            // Remove o usuário que está criando o comentário atual da lista
            $users = $users->reject(function ($user) use ($comment) {
                return $user->id === $comment->user_id;
            });

            // Remove usuários duplicados da lista
            $users = $users->unique("id");

            foreach ($users as $user) {
                $user->notify(
                    new \App\Notifications\ChallengeUserReplyCommentNotification(
                        $comment
                    )
                );
            }
        }

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
