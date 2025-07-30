<?php

namespace App\Listeners;

use App\Events\ChallengeJoined;
use App\Events\PurchaseStarted;
use App\Events\UserJoinedWorkshop;
use App\Events\UsersFirstWorkshop;
use App\Models\ChallengeUser;
use App\Services\Mail\EmailOctopusService;

class EmailOctopus
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
    public function handle(object $event): void
    {
        $emailOctopus = new EmailOctopusService;

        if ($event instanceof ChallengeJoined) {
            $event->user->id;

            $challenges = ChallengeUser::where(
                'user_id',
                $event->user->id
            )->get();

            // if is first project, update email octopus tag
            if ($challenges->count() == 1) {
                $emailOctopus->updateEmailOctopusContact(
                    $event->user->email,
                    [],
                    ['first-challenge' => true]
                );
            }
        }
        if ($event instanceof UsersFirstWorkshop) {
            $emailOctopus->updateEmailOctopusContact($event->user->email, [
                'first_workshop' => $event->workshop->name,
            ]);
        }

        if ($event instanceof UserJoinedWorkshop) {
            $emailOctopus->updateEmailOctopusContact(
                $event->user->email,
                [],
                [
                    "workshop-{$event->workshop->slug}" => true,
                ]
            );
        }

        if ($event instanceof PurchaseStarted) {
            $emailOctopus->updateEmailOctopusContact(
                $event->user->email,
                [],
                [
                    'purchase_started' => true,
                ]
            );
        }
    }
}
