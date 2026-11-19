<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $packageName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Adilisha STEM Labs plan is active',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-activated',
            with: [
                'userName' => $this->user->name,
                'packageName' => $this->packageName,
                'expiresAt' => optional($this->user->subscription_expires_at)->timezone(config('app.timezone'))?->format('d M Y H:i'),
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
