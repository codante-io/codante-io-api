<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Subscription $subscription
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Codante | Pagamento Reembolsado');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.payment-refunded');
    }
}
