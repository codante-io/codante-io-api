<?php

namespace App\Listeners;

use App\Events\AdminPublishedCertificate;
use App\Notifications\Discord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;

class CertificatePublished
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // dd("oi");
    }

    /**
     * Handle the event.
     */
    public function handle(AdminPublishedCertificate $event): void
    {
        // new Discord("TESTE", "pedidos-certificados");
        $certificate = $event->certificate;
        $certifiable = $event->certifiable;
        $certifiable_type = $event->certificate->certifiable_type;

        if ($certifiable_type === "App\\Models\\ChallengeUser") {
            $message =
                $event->certificate->status == "published"
                    ? "Projeto aprovado ✅"
                    : "Projeto não aprovado ❌";
            new Discord(
                "Certificado ID: {$certificate->id}\nStatus atualizado. $message",
                "pedidos-certificados"
            );
        }

        // Notification::send(
        //     new \App\Notifications\CertificatePublishedNotification(
        //         $certificate,
        //         $certifiable
        //     )
        // );
    }
}
