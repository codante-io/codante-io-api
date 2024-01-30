<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChallengeUserReplyCommentNotification extends Notification
{
    use Queueable;
    private $comment;
    private $commentable;
    private $challenge;
    private $challengeUser;

    /**
     * Create a new notification instance.
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
        $this->commentable = $comment->commentable;
        $this->challenge = $this->commentable->challenge;
        $this->challengeUser = $this->commentable->challengeUser;
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
        return (new MailMessage())
            ->line(
                "[Codante] Alguém comentou em uma discussão que você está participando!"
            )
            ->greeting("Olá $firstName")
            ->line(
                "Alguém respondeu um comentário na submissão de {$this->challengeUser->user->name} do Mini Projeto {$this->challenge->name}!"
            )
            ->action(
                "Ver comentário",
                $frontUrl .
                    "/mini-projetos/{$this->challenge->slug}/submissoes/{$notifiable->github_user}#comment-{$this->comment->id}"
            )
            ->line("Clique no botão acima para ver ou responder o comentário.");
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
