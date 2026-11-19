<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionRenewalReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSubscriptionRenewalRemindersCommand extends Command
{
    protected $signature = 'subscriptions:send-renewal-reminders';

    protected $description = 'Email users whose prepaid plans expire soon';

    public function handle(): int
    {
        $days = max(1, (int) config('subscriptions.renewal_reminder_days', 3));
        $windowStart = now();
        $windowEnd = now()->addDays($days);

        $users = User::query()
            ->whereIn('subscription_tier', ['trial', 'individual', 'pro'])
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [$windowStart, $windowEnd])
            ->where(function ($query): void {
                $query->whereNull('subscription_renewal_reminded_at')
                    ->orWhereColumn('subscription_renewal_reminded_at', '<', 'subscription_expires_at');
            })
            ->get();

        $count = 0;

        foreach ($users as $user) {
            if ($user->hasSubsidizedSchoolAccess() || ! $user->hasActivePrepaidAccess()) {
                continue;
            }

            if (! $user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new SubscriptionRenewalReminderMail($user));
                $user->forceFill(['subscription_renewal_reminded_at' => now()])->save();
                $count++;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $this->info("Sent {$count} renewal reminder(s).");

        return self::SUCCESS;
    }
}
