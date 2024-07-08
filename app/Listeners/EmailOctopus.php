<?php

namespace App\Listeners;

use App\Events\ChallengeJoined;
use App\Events\UserEnteredWorkshop;
use App\Events\UsersFirstWorkshop;
use App\Events\WorkshopUserCreated;
use App\Models\ChallengeUser;
use App\Models\Workshop;
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
        if ($event instanceof ChallengeJoined) {
            $event->user->id;

            $challenges = ChallengeUser::where(
                "user_id",
                $event->user->id
            )->get();

            // if is first project, update email octopus tag
            if ($challenges->count() == 1) {
                $emailOctopus = new EmailOctopusService();
                $emailOctopus->updateEmailOctopusContact(
                    $event->user->email,
                    [],
                    ["first-challenge" => true]
                );
            }
        }
        if ($event instanceof UsersFirstWorkshop) {
            $emailOctopus = new EmailOctopusService();
            $emailOctopus->updateEmailOctopusContact($event->user->email, [
                "first_workshop" => $event->workshop->name,
            ]);
        }
    }
}
