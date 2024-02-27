<?php

namespace App\Listeners;

use App\Events\UserCompletedLesson;

class WorkshopUserCreated
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
    public function handle(UserCompletedLesson $event): void
    {
        $workshop = $event->workshop;
        $user = $event->user;

        $user->workshops()->syncWithoutDetaching([$workshop->id]);

        $lessonCount = $workshop->lessons()->count();
        $completedLessons = $user
            ->lessons()
            ->where("workshop_id", $workshop->id)
            ->count();

        if ($lessonCount === $completedLessons) {
            $workshopUser = $user->workshops()->find($workshop->id);
            $user->workshops()->updateExistingPivot($workshop->id, [
                "status" => "completed",
                "completed_at" => $workshopUser->pivot->completed_at
                    ? $workshopUser->pivot->completed_at
                    : now(),
            ]);
        }
    }
}
