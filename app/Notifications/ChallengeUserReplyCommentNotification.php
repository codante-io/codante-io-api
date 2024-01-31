<?php

namespace App\Notifications;

use App\Models\ChallengeUser;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ChallengeUserReplyCommentNotification extends Notification
{
    use Queueable;
    private $parentComment;
    private $challenge;
    private $challengeUserUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        Comment $parentComment,
        ChallengeUser $challengeUser
    ) {
        $this->parentComment = $parentComment;
        $this->challenge = $challengeUser->challenge;
        $this->challengeUserUser = $challengeUser->user;
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
            ->from("contato@codante.io", "Contato Codante")
            ->subject("[Codante] Uma pessoa respondeu seu comentário!")
            ->greeting("Olá $firstName")
            ->line("Alguém respondeu a um comentário seu no Codante!")
            ->action(
                "Ver comentário",
                $frontUrl .
                    "/mini-projetos/{$this->challenge->slug}/submissoes/{$this->challengeUserUser->github_user}#comment-{$this->parentComment->id}"
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
