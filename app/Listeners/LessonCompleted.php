<?php

namespace App\Listeners;

use App\Events\UserCompletedLesson;
use App\Events\UserCompletedWorkshop;
use App\Models\Certificate;
use App\Models\WorkshopUser;
use App\Notifications\Discord;
use Notification;

class LessonCompleted
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

        if (! $workshop) {
            return;
        }

        $lessonCount = $workshop->lessons()->count();
        $completedLessons = $user
            ->lessons()
            ->where('workshop_id', $workshop->id)
            ->count();

        $user->workshops()->syncWithoutDetaching([
            $workshop->id => [
                'status' => $completedLessons >= $lessonCount
                        ? 'completed'
                        : 'in-progress',
                'completed_at' => $completedLessons >= $lessonCount ? now() : null,
                'percentage_completed' => $completedLessons >= $lessonCount
                        ? 100
                        : ($completedLessons / $lessonCount) * 100,
            ],
        ]);

        $durationInSeconds = $workshop->lessons()->sum('duration_in_seconds');

        if ($completedLessons >= $lessonCount) {
            event(new UserCompletedWorkshop($user, $workshop));
            $workshopUser = WorkshopUser::where('user_id', $user->id)
                ->where('workshop_id', $workshop->id)
                ->first();

            $certificate = Certificate::firstOrCreate(
                [
                    'certifiable_type' => "App\Models\WorkshopUser",
                    'certifiable_id' => $workshopUser->id,
                ],
                [
                    'user_id' => $user->id,
                    'status' => 'published',
                    'metadata' => [
                        'certifiable_source_name' => $workshop->name,
                        'end_date' => $workshopUser->completed_at ??
                            now()->format('Y-m-d H:i:s'),
                        'certifiable_slug' => $workshop->slug,
                        'duration_in_seconds' => $durationInSeconds,
                    ],
                ]
            );

            if ($user->is_pro) {
                if ($certificate->wasRecentlyCreated) {
                    new Discord(
                        "📔 Workshop: {$workshop->name}\n🗣️ O usuário {$user->name} completou o workshop e recebeu um certificado.\nID: {$certificate->id}",
                        'pedidos-certificados'
                    );

                    Notification::send(
                        $event->user,
                        new \App\Notifications\CertificatePublishedNotification(
                            $certificate,
                            $workshopUser
                        )
                    );
                }
            }
        }
    }
}
