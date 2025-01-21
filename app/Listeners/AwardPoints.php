<?php

namespace App\Listeners;

use App\Events\ChallengeCompleted;
use App\Events\ChallengeForked;
use App\Events\ChallengeJoined;
use App\Events\ReactionCreated;
use App\Events\ReactionDeleted;
use App\Models\UserActionPoints;

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
                10,
                $event->challenge->id,
                "App\Models\ChallengeUser"
            );
        } elseif ($event instanceof ChallengeJoined) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'challenge_joined',
                1,
                $event->challenge->id,
                "App\Models\ChallengeUser"
            );
        } elseif ($event instanceof ChallengeForked) {
            UserActionPoints::awardPoints(
                $event->user->id,
                'challenge_forked',
                3,
                $event->challenge->id,
                "App\Models\ChallengeUser"
            );
        } elseif ($event instanceof ReactionCreated) {
            UserActionPoints::awardPoints(
                $event->reactable->user_id,
                'reaction_received',
                1,
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
        }
    }
}
