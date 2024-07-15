<?php

namespace App\Listeners;

use App\Events\UserJoinedWorkshop as UserJoinedWorkshopEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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
