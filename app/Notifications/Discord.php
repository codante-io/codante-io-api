<?php

namespace App\Notifications;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class Discord extends Notification
{
    use Notifiable;
    use Queueable;

    public function __construct($message, $channel = 'notificacoes')
    {
        $channels = config('discord.channels');

        $webhookUrl = $channels[$channel];

        try {
            Http::post($webhookUrl, ['content' => $message]);
        } catch (Exception $e) {
            Mail::to("contato@trilhante.com.br")
            ->from("contato@trilhante.com.br")
            ->subject("CODANTE - Erro na notificação do Discord")
            ->html($e->getMessage());
        }
    }
}