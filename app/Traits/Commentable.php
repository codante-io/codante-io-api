<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\User;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

trait Commentable
{
    use CrudTrait;

    public function comments()
    {
        return $this->morphMany(
            Comment::class,
            'comments',
            'commentable_type',
            'commentable_id'
        );
    }

    public function createComment(
        string $comment,
        User $user,
        $replying_to = null
    ) {
        return $this->comments()->create([
            'comment' => $comment,
            'user_id' => $user->id,
            'replying_to' => $replying_to,
        ]);
    }
}
