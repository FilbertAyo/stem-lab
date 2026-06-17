<?php

namespace App\Services;

use App\Models\SubscriptionPackage;
use App\Models\User;

class SubscriptionSelectionService
{
    public function paymentsEnabled(): bool
    {
        return (bool) config('subscriptions.payments_enabled', false);
    }

    public function canUserSelectPackage(User $user, string $slug): bool
    {
        if (! $this->paymentsEnabled()) {
            return false;
        }

        if ($user->hasLockedSubscriptionPlan()) {
            return false;
        }

        return in_array($slug, ['free-trial', 'individual'], true);
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

        $user->forceFill([
            'subscription_tier' => $package->tier_key,
        ])->save();

        return $package;
    }

    public function applyPublicSchoolAccess(User $user): void
    {
        $user->forceFill([
            'subscription_tier' => 'public_school',
        ])->save();
    }
}
