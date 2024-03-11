<?php

namespace App\Listeners;

use App\Events\UserErasedLesson;

class LessonRemoved
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserErasedLesson $event): void
    {
        $workshop = $event->workshop;
        $user = $event->user;

        $user->workshops()->updateExistingPivot($workshop->id, [
            "status" => "in-progress",
        ]);
    }
}
