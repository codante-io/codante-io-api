<?php

namespace App\Services;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Collection;

class OpenClosedChallengeLessonsRobot
{
    // VARIABLES
    private static $discordChannel = 'notificacoes-site';

    private static $mainTechnologyHackatonId = 225;

    public function __construct(protected Discord $discord)
    {
    }

    public function handle()
    {
        // Get all challenges with their workshops // only status published.
        /** @var Collection<Challenge> $challenges */
        $challenges = Challenge::with(['lessons'])->where('status', 'published')->get();

        // Discord Message - Start
        $this->discord->notify(
            '==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====',
            self::$discordChannel
        );

        foreach ($challenges as $challenge) {
            $this->checkChallenge($challenge);
        }

        // Discord Message - End
        $this->discord->notify(
            '==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====',
            self::$discordChannel
        );
    }

    public function checkChallenge(Challenge $challenge)
    {
        // if there is no lessons AND the main category is not hackaton. Send a message to the discord.
        if ($challenge->lessons->isEmpty() && $challenge->main_technology_id !== self::$mainTechnologyHackatonId) {
            $this->discord->notify(
                "- MP: **{$challenge->name}**\n".
                "  - Problema: MP publicado mas não possui aulas associadas\n".
                "  - Link: <https://codante.io/mini-projetos/{$challenge->slug}>\n\n",
                self::$discordChannel);
        }

        // if the challenge is open, all the lessons should be open too.
        if (! $challenge->is_premium && $challenge->lessons->contains(function ($lesson) {
            return $lesson->available_to !== 'all' && $lesson->available_to !== 'logged_in';
        })) {
            $this->discord->notify(
                "- MP: **{$challenge->name}**\n".
                "  - Problema: MP aberto mas existem aulas que não estão disponíveis para todos os usuários\n".
                "  - Link: <https://codante.io/mini-projetos/{$challenge->slug}>\n\n",
                self::$discordChannel);
        }

        // if the challenge is closed, at least one lesson should be closed too.
        if ($challenge->is_premium && $challenge->lessons->every(function ($lesson) {
            return $lesson->available_to === 'all' || $lesson->available_to === 'logged_in';
        })) {
            $this->discord->notify(
                "- MP: **{$challenge->name}**\n".
                "  - Problema: MP fechado mas todas as aulas estão abertas\n".
                "  - Link: <https://codante.io/mini-projetos/{$challenge->slug}>\n\n",
                self::$discordChannel);
        }
    }
}
