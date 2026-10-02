<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAttentionDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<array{title: string, count: int, items: list<string>, action_url?: string|null, action_label?: string|null}>  $sections
     */
    public function __construct(
        public string $categoryKey,
        public string $categoryLabel,
        public array $sections,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily digest · '.$this->categoryLabel.' · '.now()->format('M d, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-attention-digest',
        );
    }
}
