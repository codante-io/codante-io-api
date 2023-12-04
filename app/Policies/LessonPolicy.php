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
        if ($lesson->available_to === "all") {
            return true;
        }

        if ($lesson->available_to === "logged_in" && $user) {
            return true;
        }

        if ($lesson->available_to === "pro" && $user && $user->is_pro) {
            return true;
        }

        return false;
    }
}
