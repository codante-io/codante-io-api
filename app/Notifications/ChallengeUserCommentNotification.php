<?php

namespace App\Notifications;

use App\Models\ChallengeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChallengeUserCommentNotification extends Notification
{
    use Queueable;

    private $comment;

    private $challengeUser;

    private $challenge;

    /**
     * Create a new notification instance.
     */
    public function __construct($comment, ChallengeUser $challengeUser)
    {
        $this->comment = $comment;
        $this->challengeUser = $challengeUser;
        $this->challenge = $this->challengeUser->challenge;
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
        $frontUrl = config('app.frontend_url');

        return (new MailMessage)
            ->from('contato@codante.io', 'Codante')
            ->subject('[Codante] Alguém comentou na sua submissão!')
            ->greeting("Olá $firstName")
            ->line(
                "Alguém comentou na sua submissão do Mini Projeto {$this->challenge->name}!"
            )
            ->action(
                'Ver comentário',
                $frontUrl.
                    "/mini-projetos/{$this->challenge->slug}/submissoes/{$notifiable->github_user}#comment-{$this->comment->id}"
            )
            ->line('Clique no botão acima para ver ou responder o comentário.');
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
