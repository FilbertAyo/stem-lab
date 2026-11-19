<?php

namespace App\Services;

use App\Mail\SubscriptionActivatedMail;
use App\Mail\SubscriptionPaymentDueMail;
use App\Models\SubscriptionOrder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SubscriptionMailer
{
    public function sendPaymentDue(SubscriptionOrder $order): void
    {
        $order->loadMissing('user');

        if (! $order->user?->email) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new SubscriptionPaymentDueMail($order));

            $order->forceFill([
                'metadata' => array_merge($order->metadata ?? [], [
                    'payment_due_notified_at' => now()->toIso8601String(),
                ]),
            ])->save();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function sendActivated(User $user, string $packageName): void
    {
        if (! $user->email) {
            return;
        }

        try {
            Mail::to($user->email)->send(new SubscriptionActivatedMail($user, $packageName));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function sendPaymentReminder(SubscriptionOrder $order): void
    {
        $order->loadMissing('user');

        if (! $order->user?->email || ! $order->isOpen()) {
            return;
        }

        try {
            Mail::to($order->user->email)->send(new SubscriptionPaymentDueMail($order));

            $order->forceFill([
                'metadata' => array_merge($order->metadata ?? [], [
                    'payment_reminded_at' => now()->toIso8601String(),
                ]),
            ])->save();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
