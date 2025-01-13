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

    public function __construct(
        $message,
        $channel = 'notificacoes',
        $embeds = null
    ) {
        $channels = config('discord.channels');

        // Check if the current environment is 'local'
        if (config('app.env') === 'local' || config('app.env') === 'testing') {
            // Use the test webhook URL
            $webhookUrl = $channels['teste'];
        } else {
            // Use the provided channel
            $webhookUrl = $channels[$channel];
        }

        if (! $webhookUrl) {
            throw new Exception(
                'No test webhook URL found in config/discord.php'
            );
        }

        try {
            Http::post($webhookUrl, [
                'content' => $message,
                'embeds' => $embeds,
            ]);
        } catch (Exception $e) {
            Mail::to('contato@trilhante.com.br')
                ->from('contato@trilhante.com.br')
                ->subject('CODANTE - Erro na notificação do Discord')
                ->html($e->getMessage());
        }
    }
}
