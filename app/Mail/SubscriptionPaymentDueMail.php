<?php

namespace App\Mail;

use App\Models\SubscriptionOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentDueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SubscriptionOrder $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complete your Adilisha STEM Labs payment',
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing('user', 'package');

        $payUrl = filled($this->order->checkout_url)
            ? $this->order->checkout_url
            : route('dashboard').'#platform-subscription';

        return new Content(
            view: 'emails.subscription-payment-due',
            with: [
                'userName' => $this->order->user?->name,
                'packageName' => $this->order->package?->name ?? 'Individual',
                'amount' => number_format((int) $this->order->amount_tzs),
                'currency' => $this->order->currency,
                'periodDays' => (int) $this->order->period_days,
                'expiresAt' => optional($this->order->expires_at)->timezone(config('app.timezone'))?->format('d M Y H:i'),
                'payUrl' => $payUrl,
            ],
        );
    }
}
