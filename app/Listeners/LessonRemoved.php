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

        // // if there is no workshop - dont do anything.
        if (! $workshop) {
            return;
        }

        $lessonCount = $workshop->lessons()->count();

        $completedLessons = $user
            ->lessons()
            ->where('lessonable_id', $workshop->id)
            ->where('lessonable_type', get_class($workshop))
            ->count();

        $user->workshops()->updateExistingPivot($workshop->id, [
            'status' => 'in-progress',
            'percentage_completed' => $lessonCount > 0 ? ($completedLessons / $lessonCount) * 100 : 0,
        ]);
    }
}
