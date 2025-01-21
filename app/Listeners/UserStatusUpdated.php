<?php

namespace App\Listeners;

use App\Events\UserStatusUpdated as UserStatusUpdatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserStatusUpdated implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(UserStatusUpdatedEvent $event): void
    {
        $this->updateUserInEmailList($event->user);
    }

    private function updateUserInEmailList($user): void
    {
        (new \App\Services\Mail\EmailOctopusService())->updateUser($user);
    }
}
