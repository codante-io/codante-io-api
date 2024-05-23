<?php

namespace App\Listeners;

use App\Models\Leads;
use Illuminate\Auth\Events\Registered as RegisteredEvent;
use App\Notifications\Discord;
use App\Services\Mail\EmailOctopusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class Registered implements ShouldQueue
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
    public function handle(RegisteredEvent $event): void
    {
        // Send the Discord Notification
        $this->sendDiscordNotification($event);

        // Add the user to the email list
        $this->addToEmailList($event->user);
    }

    private function sendDiscordNotification(RegisteredEvent $event): void
    {
        $message =
            "Novo usuário cadastrado 🥳 \n " .
            $event->user->name .
            " | " .
            $event->user->email;
        new Discord($message);
    }

    private function addToEmailList($user): void
    {
        $lead = Leads::where("email", $user->email)->first();

        if ($lead) {
            (new EmailOctopusService())->updateLeadAfterSignUp($user);
        } else {
            (new EmailOctopusService())->addUser($user);
        }
    }
}
