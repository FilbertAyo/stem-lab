<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Adilisha STEM Labs plan has expired',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expired',
            with: [
                'userName' => $this->user->name,
                'dashboardUrl' => route('dashboard').'#platform-subscription',
            ],
        );
    }
}
