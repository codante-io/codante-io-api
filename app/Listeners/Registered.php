<?php

namespace App\Listeners;

use App\Events\Auth\Registered as RegisteredEvent;
use App\Notifications\Discord;
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
        Auth::login($event->user);

        $message = "Novo usuário cadastrado 🥳 \n " . $event->user->name . '|' . $event->user->email;

        new Discord($message);
    }
}
