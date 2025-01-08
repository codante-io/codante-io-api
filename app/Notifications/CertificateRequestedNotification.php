<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CertificateRequestedNotification extends Notification
{
    use Queueable;

    private $certificate;

    // private $challenge;
    /**
     * Create a new notification instance.
     */
    public function __construct($certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $firstName = Str::title(explode(' ', $notifiable->name)[0]);
        $message =
            $this->certificate->certifiable_type === "App\Models\ChallengeUser"
                ? "Recebemos a sua solicitação de certificado para o Mini Projeto {$this->certificate->certifiable->challenge->name}!"
                : "Seu certificado com ID {$this->certificate->id} foi solicitado e em breve estará disponível!";

        return (new MailMessage())
            ->from('contato@codante.io', 'Codante')
            ->subject('[Codante] Recebemos sua solicitação de certificado!')
            ->greeting("Olá $firstName")
            ->line($message)
            ->line(
                'Avisaremos você assim que ele estiver disponível. O prazo de publicação é de 3 dias úteis'
            );
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
