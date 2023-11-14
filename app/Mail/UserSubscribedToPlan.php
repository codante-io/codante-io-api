<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserSubscribedToPlan extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Subscription $subscription
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Codante | Assinatura Realizada");
    }

    public function content(): Content
    {
        // se for boleto bancário, a view com o botão de pagamento é diferente
        if (strtolower($this->subscription->payment_method) === "boleto") {
            return new Content(
                markdown: "emails.user-subscribed-to-plan-boleto"
            );
        }

        // se for boleto bancário, a view com o botão de pagamento é diferente
        if (strtolower($this->subscription->payment_method) === "pix") {
            return new Content(markdown: "emails.user-subscribed-to-plan-pix");
        }

        return new Content(markdown: "emails.user-subscribed-to-plan");
    }
}
