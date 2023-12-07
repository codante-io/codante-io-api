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
            "{$this->getRandomMessageGreeting()}O Mini Projeto **{$event->challenge->name}** foi concluído por **{$event->user->name}**\n ​ \n",
            "submissoes",
            [
                [
                    "title" => "Submissão de {$event->user->name}",
                    "description" => "Mini Projeto: {$event->challenge->name}",
                    "url" => "https://codante.io/mini-projetos/{$event->challenge->slug}/submissoes",
                    "color" => 0x0099ff,
                    "image" => [
                        "url" =>
                            $event->challengeUser->pivot->submission_image_url,
                    ],
                ],
            ]
        );
    }

    private function getRandomMessageGreeting()
    {
        $messages = [
            "🎉 __Nova Submissão Enviada__!\n ​ \n",
            "👉🏽 __Mais um projeto concluído com sucesso__!\n ​ \n",
            "🧑🏽‍💻 __Praticando é que se  evolui__!\n ​ \n",
        ];
        return $messages[array_rand($messages)];
    }
}
