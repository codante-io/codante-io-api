<?php

namespace App\Services;

use App\Models\Challenge;
use App\Notifications\Discord;
use Illuminate\Database\Eloquent\Collection;

class OpenClosedChallengeLessonsRobot
{

    // VARIABLES
    private static $discordChannel = 'notificacoes-site';
    private static $mainTechnologyHackatonId = 225;


    public static function handle()
    {
        // Get all challenges with their workshops // only status published. 
        /** @var Collection<Challenge> $challenges */
        $challenges = Challenge::with(['lessons'])->where('status', 'published')->get();

        // if there is no lessons, send a message to the discord
        



        // Discord Message - Start
        new Discord(
            '==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====',
            'notificacoes-site'
        );



        foreach ($challenges as $challenge) {
            self::checkChallenge($challenge);
        }

        // Discord Message - End
        new Discord(
            '==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====',
            'notificacoes-site'
        );
    }

    private static function checkChallenge(Challenge $challenge)
    {
        // if there is no lessons AND the main category is not hackaton. Send a message to the discord.
        if ($challenge->lessons->isEmpty() && $challenge->main_technology_id !== self::$mainTechnologyHackatonId) {
            new Discord(
                "- MP: **{$challenge->name}**\n" .
                "  - Problema: MP publicado mas não possui aulas associadas\n" .
                "  - Link: <https://codante.io/mini-projetos/{$challenge->slug}>\n\n",
                self::$discordChannel);  
        }


        // if the challenge is open, all the lessons should be open too.
        if (!$challenge->is_premium && $challenge->lessons->contains(function ($lesson) {
            return $lesson->available_to !== 'all' && $lesson->available_to !== 'logged_in';
        })) {
            new Discord(
                
                "- MP: **{$challenge->name}**\n" .
                "  - Problema: MP aberto mas existem aulas que não estão disponíveis para todos os usuários\n" .
                "  - Link: <https://codante.io/mini-projetos/{$challenge->slug}>\n\n",
                self::$discordChannel); 
        }

        // if the challenge is closed, at least one lesson should be closed too.
        if ($challenge->is_premium && $challenge->lessons->every(function ($lesson) {
            return $lesson->available_to === 'all' || $lesson->available_to === 'logged_in';
        })) {
            new Discord(
                "- MP: **{$challenge->name}**\n" .
                "  - Problema: MP fechado mas todas as aulas estão abertas\n" .
                "  - Link: <https://codante.io/mini-projetos/{$challenge->slug}>\n\n",
                self::$discordChannel);
        }
    }
}
