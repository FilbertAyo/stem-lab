<?php

namespace App\Services;

use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionSelectionService
{
    public function __construct(
        private readonly SubscriptionMailer $subscriptionMailer,
    ) {}

    public function paymentsEnabled(): bool
    {
        return (bool) config('subscriptions.payments_enabled', false);
    }

    public function entitlementsEnforced(): bool
    {
        return $this->paymentsEnabled();
    }

    public function canUserSelectPackage(User $user, string $slug): bool
    {
        if (! $this->paymentsEnabled()) {
            return false;
        }

        if ($user->hasLockedSubscriptionPlan()) {
            return false;
        }

        return in_array($slug, ['free-trial', 'individual', 'individual-yearly'], true);
    }

    public function selectBySlug(User $user, string $slug): SubscriptionPackage
    {
        if (! $this->canUserSelectPackage($user, $slug)) {
            abort(403, 'Subscription selection is not available.');
        }

        $package = SubscriptionPackage::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        if ($package->slug === 'public-school') {
            abort(422, 'Public school access is granted through club membership.');
        }

        if (in_array($package->slug, ['individual', 'individual-yearly'], true)) {
            abort(422, 'Individual packages require Snippe checkout.');
        }

        if ($package->slug === 'free-trial') {
            return $this->activateFreeTrial($user, $package);
        }

        abort(422, 'Unsupported package selection.');
    }

    public function activateFreeTrial(User $user, ?SubscriptionPackage $package = null): SubscriptionPackage
    {
        $package ??= SubscriptionPackage::query()
            ->active()
            ->where('slug', 'free-trial')
            ->firstOrFail();

        if ($user->hasLockedSubscriptionPlan()) {
            abort(403, 'You already have an active plan through your STEM club.');
        }

        if ($user->trial_used_at !== null) {
            abort(422, 'You have already used your free trial.');
        }

        if ($user->hasActivePrepaidAccess()) {
            abort(422, 'You already have an active subscription.');
        }

        $periodDays = max(1, (int) $package->billing_period_days ?: 7);

        DB::transaction(function () use ($user, $periodDays): void {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->trial_used_at !== null) {
                abort(422, 'You have already used your free trial.');
            }

            $locked->forceFill([
                'subscription_tier' => 'trial',
                'subscription_expires_at' => now()->addDays($periodDays),
                'trial_used_at' => now(),
                'subscription_renewal_reminded_at' => null,
            ])->save();
        });

        $user->refresh();
        $this->subscriptionMailer->sendActivated($user, $package->name);

        return $package;
    }

    /**
     * Start a 7-day trial for a newly registered user. Never throws — signup must succeed.
     */
    public function grantTrialOnSignup(User $user): void
    {
        if (! $this->paymentsEnabled()) {
            return;
        }

        if ($user->hasLockedSubscriptionPlan() || $user->trial_used_at !== null || $user->hasActivePrepaidAccess()) {
            return;
        }

        $package = SubscriptionPackage::query()
            ->active()
            ->where('slug', 'free-trial')
            ->first();

        if (! $package) {
            return;
        }

        try {
            $this->activateFreeTrial($user, $package);
            $user->refresh();
        } catch (\Throwable) {
            // Registration must not fail because trial activation failed.
        }
    }

    public function applyPublicSchoolAccess(User $user): void
    {
        $user->forceFill([
            'subscription_tier' => 'public_school',
            'subscription_expires_at' => null,
            'subscription_renewal_reminded_at' => null,
        ])->save();
    }
}
