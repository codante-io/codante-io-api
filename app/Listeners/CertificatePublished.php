<?php

namespace App\Listeners;

use App\Events\AdminPublishedCertificate;
use App\Services\Discord as DiscordService;
use Notification;

class CertificatePublished
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
    public function handle(AdminPublishedCertificate $event): void
    {
        $certificate = $event->certificate;
        $certifiable = $event->certifiable;
        $certifiable_type = $event->certificate->certifiable_type;

        if (
            $certifiable_type === "App\Models\ChallengeUser" &&
            $event->certificate->status === 'published'
        ) {
            $message = "Certificado ID: {$certificate->id}\nStatus atualizado. Projeto aprovado ✅";
            DiscordService::sendMessage($message, 'pedidos-certificados');

            Notification::send(
                $certifiable->user,
                new \App\Notifications\CertificatePublishedNotification(
                    $certificate,
                    $certifiable
                )
            );
        } elseif (
            $certifiable_type === 'App\\Models\\ChallengeUser' &&
            $event->certificate->status !== 'published'
        ) {
            $message = "Certificado ID: {$certificate->id}\nStatus atualizado para {$event->certificate->status} ❌";
            DiscordService::sendMessage($message, 'pedidos-certificados');
        }
    }
}
