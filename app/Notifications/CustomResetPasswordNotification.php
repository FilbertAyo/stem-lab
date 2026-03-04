<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token
    ) {}

    /**
     * Get the token (for testing and URL building).
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get the reset URL for the given notifiable.
     */
    protected function resetUrl(mixed $notifiable): string
    {
        return URL::route('password.reset', ['token' => $this->getToken()])
            . '?email=' . urlencode($notifiable->getEmailForPasswordReset());
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
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Your Password – ' . config('mail-branding.app_name'))
            ->view('emails.reset-password', ['resetUrl' => $resetUrl]);
    }
}
