<?php

namespace App\Services;

use App\Models\SubscriptionPackage;
use App\Models\User;

class SubscriptionSelectionService
{
    public function selectBySlug(User $user, string $slug): SubscriptionPackage
    {
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
