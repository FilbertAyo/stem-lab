<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MarkLegacyTrialsUsedCommand extends Command
{
    protected $signature = 'subscriptions:mark-legacy-trials-used';

    protected $description = 'Mark existing free accounts as having used the 7-day trial so they must pay or join a STEM club';

    public function handle(): int
    {
        $query = User::query()
            ->whereNull('trial_used_at')
            ->where(function ($inner): void {
                $inner->whereNull('subscription_tier')
                    ->orWhereNotIn('subscription_tier', ['public_school']);
            })
            ->where(function ($inner): void {
                $inner->whereNull('subscription_expires_at')
                    ->orWhere('subscription_expires_at', '<=', now());
            })
            ->whereDoesntHave('teacherClubs', fn ($membership) => $membership->where('status', 'approved'))
            ->whereDoesntHave('linkedRosterStudent');

        $count = 0;

        $query->orderBy('id')->chunkById(200, function ($users) use (&$count): void {
            foreach ($users as $user) {
                if ($user->hasSubsidizedSchoolAccess() || $user->hasActivePrepaidAccess()) {
                    continue;
                }

                $user->forceFill([
                    'trial_used_at' => $user->created_at ?? now(),
                ])->save();

                $count++;
            }
        });

        $this->info("Marked {$count} existing account(s) as trial-used.");

        return self::SUCCESS;
    }
}
