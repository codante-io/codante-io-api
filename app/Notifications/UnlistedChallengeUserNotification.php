<?php

namespace App\Notifications;

use App\Models\ChallengeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UnlistedChallengeUserNotification extends Notification
{
    use Queueable;

    private $challengeUser;

    private $challenge;

    private $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(ChallengeUser $challengeUser)
    {
        $this->challengeUser = $challengeUser;
        $this->challenge = $this->challengeUser->challenge;
        $this->user = $this->challengeUser->user;
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
        $firstName = Str::title(
            explode(' ', $this->challengeUser->user->name)[0]
        );

        $frontUrl = config('app.frontend_url');

        return (new MailMessage)
            ->from('contato@codante.io', 'Codante')
            ->subject('[Codante] Encontramos um problema na sua submissão!')
            ->greeting("Olá $firstName")
            ->line(
                "Encontramos um problema na sua submissão do projeto {$this->challengeUser->challenge->name} e ela deixou de ser listada na nossa plataforma."
            )
            ->line(
                'Para que o seu projeto seja listado novamente, basta editar a sua submissão com o link atualizado do seu deploy. Em caso de dúvidas, entre em contato com a nossa equipe!'
            )
            ->action(
                'Ver submissão',
                $frontUrl.
                    "/mini-projetos/{$this->challengeUser->challenge->slug}/submissoes/{$this->challengeUser->user->github_user}"
            )
            ->line('Clique no botão acima para ver e editar a sua submissão.');
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
