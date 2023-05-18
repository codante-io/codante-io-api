<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered as RegisteredEvent;
use App\Jobs\ProcessRegisteredUser;
use Illuminate\Support\Facades\Auth;

class Registered
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
    public function handle(RegisteredEvent $event): void
    {
        ProcessRegisteredUser::dispatch($event->user);
    }
}
