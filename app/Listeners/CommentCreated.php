<?php

namespace App\Listeners;

use App\Events\UserCommented;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;

class CommentCreated implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserCommented $event): void
    {
        $replyingTo = $event->comment->replying_to;
        $comment = $event->comment;
        $commentable = $event->commentable;
        $commentableClass = $comment->commentable_type;

        // Send Discord notification
        new Discord(
            "💬 Um novo comentário foi feito por {$event->user->name} em {$event->comment->commentable_type} {$event->comment->commentable_id} {replying to - $replyingTo}: {$event->comment->comment}\n🔗<".
                $event->comment->commentable_url.
                '>',
            'notificacoes-comentarios'
        );

        // Send Email notification to the author of Submission. If the comment is a reply, don't send anything.
        if (
            $commentableClass === "App\Models\ChallengeUser" &&
            $replyingTo === null
        ) {
            Notification::send(
                $commentable->user,
                new \App\Notifications\ChallengeUserCommentNotification(
                    $comment,
                    $commentable
                )
            );
        }

        // Send Email notification to the author of the parent comment and all other users who commented on the same parent comment. Only if the comment is a reply.
        if ($replyingTo) {
            $parentComment = Comment::findOrFail($replyingTo);
            $relatedComments = Comment::where(
                'replying_to',
                $replyingTo
            )->get();

            $parentCommentUserID = $parentComment->user_id;
            $relatedCommentsUserIDs = $relatedComments->pluck('user_id');

            $uniqueUserIDs = $relatedCommentsUserIDs
                ->push($parentCommentUserID)
                ->unique();

            // Remove own user id from the list
            $uniqueUserIDs = $uniqueUserIDs->reject(function ($userID) use (
                $comment
            ) {
                return $userID === $comment->user_id;
            });

            $users = User::whereIn('id', $uniqueUserIDs)->get();

            foreach ($users as $user) {
                // Send Email notification to the author of the parent comment and all other users who commented on the same parent comment.
                Notification::send(
                    $user,
                    new \App\Notifications\ChallengeUserReplyCommentNotification(
                        $parentComment,
                        $commentable,
                        $comment
                    )
                );
            }
        }
    }
}
