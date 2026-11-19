<?php

namespace App\Console\Commands;

use App\Models\SubscriptionOrder;
use App\Services\SubscriptionMailer;
use Illuminate\Console\Command;

class SendSubscriptionPaymentRemindersCommand extends Command
{
    protected $signature = 'subscriptions:send-payment-reminders';

    protected $description = 'Email reminders for open subscription checkouts that still need payment';

    public function handle(SubscriptionMailer $mailer): int
    {
        $hours = max(1, (int) config('subscriptions.payment_reminder_hours', 6));
        $cutoff = now()->addHours($hours);

        $orders = SubscriptionOrder::query()
            ->with(['user', 'package'])
            ->open()
            ->whereNotNull('checkout_url')
            ->where(function ($query) use ($cutoff): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '<=', $cutoff);
            })
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            if (filled(data_get($order->metadata, 'payment_reminded_at'))) {
                continue;
            }

            $mailer->sendPaymentReminder($order);

            if ($order->expires_at && $order->expires_at->isPast()) {
                $order->refresh();
                if ($order->isOpen()) {
                    $order->forceFill([
                        'status' => SubscriptionOrder::STATUS_EXPIRED,
                    ])->save();
                }
            }

            $count++;
        }

        $this->info("Sent {$count} payment reminder(s).");

        return self::SUCCESS;
    }
}
