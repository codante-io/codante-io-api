<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\User;

trait Commentable
{
    public function comments()
    {
        return $this->morphMany(
            Comment::class,
            "comments",
            "commentable_type",
            "commentable_id"
        );
    }

    public function comment(string $comment, User $user)
    {
        // return $this->reactions()->create([
        //     "reaction" => $reaction,
        //     "user_id" => $user->id,
        // ]);
    }
}
