<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiredMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProcessSubscriptionExpiriesCommand extends Command
{
    protected $signature = 'subscriptions:process-expiries';

    protected $description = 'Notify users whose prepaid subscriptions have just expired';

    public function handle(): int
    {
        $users = User::query()
            ->whereIn('subscription_tier', ['trial', 'individual', 'pro'])
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<=', now())
            ->where(function ($query): void {
                $query->whereNull('subscription_renewal_reminded_at')
                    ->orWhereColumn('subscription_renewal_reminded_at', '<', 'subscription_expires_at');
            })
            ->get();

        $count = 0;

        foreach ($users as $user) {
            if ($user->hasSubsidizedSchoolAccess()) {
                continue;
            }

            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new SubscriptionExpiredMail($user));
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            $user->forceFill([
                'subscription_renewal_reminded_at' => now(),
            ])->save();

            $count++;
        }

        $this->info("Processed {$count} expired subscription notification(s).");

        return self::SUCCESS;
    }
}
