<?php

namespace App\Listeners;

use App\Events\ChallengeCompleted;
use App\Events\ChallengeForked;
use App\Events\ChallengeJoined;
use App\Events\ReactionCreated;
use App\Events\ReactionDeleted;
use App\Events\UserCompletedLesson;
use App\Events\UserCompletedWorkshop;
use App\Events\UserJoinedWorkshop;
use App\Models\UserActionPoints;


class PointsConfig {
    public static $points = [
        'challenge_completed' => [
            'newbie' => 100,
            'intermediate' => 200,
            'advanced' => 300,
        ],
        'challenge_joined' => 10,
        'challenge_forked' => 30,
        'reaction_received' => 1,
        'workshop_joined' => 10,
        'lesson_completed' => 10,
        'workshop_completed' => 100,
    ];
}

class AwardPoints
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof ChallengeCompleted) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'challenge_completed',
                PointsConfig::$points['challenge_completed'][$event->challenge->difficulty],
                $event->challenge->id,
                "App\Models\ChallengeUser"
            );
        } elseif ($event instanceof ChallengeJoined) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'challenge_joined',
                PointsConfig::$points['challenge_joined'],
                $event->challenge->id,
                "App\Models\ChallengeUser"
            );
        } elseif ($event instanceof ChallengeForked) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'challenge_forked',
                PointsConfig::$points['challenge_forked'],
                $event->challenge->id,
                "App\Models\ChallengeUser"
            );
        } elseif ($event instanceof ReactionCreated) {
            UserActionPoints::awardPoints(
                $event->reactable->user_id,
                'reaction_received',
                PointsConfig::$points['reaction_received'],
                $event->reactionId,
                "App\Models\Reaction"
            );
        } elseif ($event instanceof ReactionDeleted) {
            UserActionPoints::removePoints(
                $event->reactable->user_id,
                'reaction_received',
                $event->reactionId,
                "App\Models\Reaction"
            );
        } elseif ($event instanceof UserJoinedWorkshop) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'workshop_joined',
                PointsConfig::$points['workshop_joined'],
                $event->workshop->id,
                "App\Models\WorkshopUser"
            );
        } elseif ($event instanceof UserCompletedLesson) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'lesson_completed',
                PointsConfig::$points['lesson_completed'],
                $event->lesson->id,
                "App\Models\LessonUser"
            );
        } elseif ($event instanceof UserCompletedWorkshop) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'workshop_completed',
                PointsConfig::$points['workshop_completed'],
                $event->workshop->id,
                "App\Models\WorkshopUser"
            );
        }
    }
}

