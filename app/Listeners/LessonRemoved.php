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

        $lessonCount = $workshop->lessons()->count();
        $completedLessons = $user
            ->lessons()
            ->where('workshop_id', $workshop->id)
            ->count();

            $user->workshops()->updateExistingPivot($workshop->id, [
                "status" => "in-progress",
                "percentage_completed" => $lessonCount > 0 ? ($completedLessons / $lessonCount) * 100 : 0,
            ]);
    }
}
