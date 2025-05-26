<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class Discord
{
    public static function sendMessage($message, $channel = 'notificacoes', $embeds = null): void
    {
        $webhookUrl = self::getWebhookUrl($channel);

        try {
            Http::post($webhookUrl, [
                'content' => $message,
                'embeds' => $embeds,
            ]);
        } catch (Exception $e) {
            Mail::raw($e->getMessage(), function ($message) {
                $message->to('contato@trilhante.com.br')
                    ->subject('CODANTE - Erro na notificação do Discord');
            });
        }
    }

    public function notify($message, $channel = 'notificacoes', $embeds = null): void
    {
        self::sendMessage($message, $channel, $embeds);
    }

    private static function getWebhookUrl(string $channel): string
    {
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

        return $webhookUrl;
    }
}
