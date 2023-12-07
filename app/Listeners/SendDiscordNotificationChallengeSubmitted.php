<?php

namespace App\Listeners;

use App\Events\ChallengeCompleted;
use App\Notifications\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDiscordNotificationChallengeSubmitted
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
    public function handle(ChallengeCompleted $event): void
    {
        // Discord Message
        new Discord(
            "🎉 __Nova Submissão Enviada__!\nO Mini Projeto **{$event->challenge->name}** foi concluído por **{$event->user->name}**\n[Vem dar uma espiada!](https://codante.io/mini-projetos/{$event->challenge->slug}/submissoes)!",
            "submissoes"
        );
    }
}
