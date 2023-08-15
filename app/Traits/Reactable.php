<?php

namespace App\Traits;

use App\Models\Reaction;
use App\Models\User;

trait Reactable
{
    public function reactions()
    {
        return $this->morphMany(
            Reaction::class,
            "reactions",
            "reactable_type",
            "reactable_id"
        );
    }

    public function isReactedBy(string $reaction, User $user)
    {
        return $this->reactions()
            ->where("user_id", $user->id)
            ->where("reaction", $reaction)
            ->exists();
    }

    public function removeReaction(string $reaction, User $user)
    {
        $reactionRecord = $this->reactions()
            ->where("user_id", $user->id)
            ->where("reaction", $reaction)
            ->first();

        if ($reactionRecord) {
            $reactionId = $reactionRecord->id;
            $reactionRecord->delete();
            return $reactionId;
        }

        return null;
    }

    public function react(string $reaction, User $user)
    {
        return $this->reactions()->create([
            "reaction" => $reaction,
            "user_id" => $user->id,
        ]);
    }
}
