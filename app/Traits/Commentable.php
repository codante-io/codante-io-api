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

    public function create(string $comment, User $user)
    {
        return $this->comments()->create([
            "comment" => $comment,
            "user_id" => $user->id,
        ]);
    }
}
