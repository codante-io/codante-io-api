<?php

namespace App\Listeners;

use App\Events\UserJoinedWorkshop as UserJoinedWorkshopEvent;

class UserJoinedWorkshop
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
    public function handle(UserJoinedWorkshopEvent $event): void
    {
        $user = $event->user;
        $workshop = $event->workshop;

        // Send an email to the user
        $user->notify(new \App\Notifications\UserJoinedWorkshop($workshop));
    }
}
