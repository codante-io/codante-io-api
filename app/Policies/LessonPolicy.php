<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Lesson $lesson): bool
    {
        // Check if any of the user's subscriptions are active
        return $user->subscriptions->contains(function ($subscription) {
            return $subscription->status === "active";
        }) || $lesson->is_free;
    }
}
