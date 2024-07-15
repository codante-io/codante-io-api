<?php

namespace App\Notifications;

use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserJoinedWorkshop extends Notification implements ShouldQueue
{
    use Queueable;

    protected Workshop $workshop;
    /**
     * Create a new notification instance.
     */
    public function __construct(Workshop $workshop)
    {
        $this->workshop = $workshop;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->workshop->is_standalone ? "workshop" : "tutorial";

        return (new MailMessage())
            ->subject("Você está participando de um novo " . $type . "!")
            ->markdown("emails.user-joined-workshop", [
                "workshop" => $this->workshop,
                "user" => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
                //
            ];
    }
}
