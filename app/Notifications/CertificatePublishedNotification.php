<?php

namespace App\Notifications;

use App\Models\ChallengeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CertificatePublishedNotification extends Notification
{
    use Queueable;

    private $certificate;
    private $certifiable;
    // private $challenge;
    /**
     * Create a new notification instance.
     */
    public function __construct($certificate, $certifiable)
    {
        $this->certificate = $certificate;
        $this->certifiable = $certifiable;
        // $this->challenge = $this->challengeUser->challenge;
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
        $firstName = Str::title(explode(" ", $notifiable->name)[0]);
        $frontUrl = config("app.frontend_url");

        if (
            $this->certificate->certifiable_type === "App\Models\ChallengeUser"
        ) {
            $message = "Seu certificado para o Mini Projeto {$this->certificate->certifiable->challenge->name} foi publicado!";
        } elseif (
            $this->certificate->certifiable_type === "App\Models\WorkshopUser"
        ) {
            $message = "Você finalizou o Workshop {$this->certificate->certifiable->workshop->name} e seu certificado foi publicado!";
        } else {
            $message = "Seu certificado com ID {$this->certificate->id} foi publicado!";
        }

        return (new MailMessage())
            ->from("contato@codante.io", "Contato Codante")
            ->subject("[Codante] Seu certificado foi publicado!")
            ->greeting("Olá $firstName")
            ->line($message)
            ->action(
                "Ver certificado",
                $frontUrl . "/certificados/{$this->certificate->id}"
            )
            ->line("Clique no botão acima para acessá-lo.");
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
