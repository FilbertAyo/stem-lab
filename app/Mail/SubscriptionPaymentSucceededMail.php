<?php

namespace App\Mail;

use App\Models\SubscriptionOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentSucceededMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SubscriptionOrder $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment confirmed — your Adilisha STEM Labs plan is active',
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing('user', 'package');

        return new Content(
            view: 'emails.subscription-payment-succeeded',
            with: [
                'userName' => $this->order->user?->name,
                'packageName' => $this->order->package?->name ?? 'Individual',
                'amount' => number_format((int) $this->order->amount_tzs),
                'currency' => $this->order->currency,
                'expiresAt' => optional($this->order->user?->subscription_expires_at)->timezone(config('app.timezone'))?->format('d M Y H:i'),
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
