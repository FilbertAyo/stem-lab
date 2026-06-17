<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionSelectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionPackageController extends Controller
{
    public function select(Request $request, SubscriptionSelectionService $selectionService): RedirectResponse
    {
        if (! $selectionService->paymentsEnabled()) {
            return redirect()
                ->to(url()->previous().'#platform-subscription')
                ->with('error', 'Online subscription is not available yet. Please check back soon.');
        }

        $validated = $request->validate([
            'package' => [
                'required',
                'string',
                Rule::in(['free-trial', 'individual']),
            ],
        ]);

        $user = $request->user();

        if ($user->hasLockedSubscriptionPlan()) {
            return redirect()
                ->to(url()->previous().'#platform-subscription')
                ->with('error', 'You already have an active plan through your STEM club.');
        }

        $package = $selectionService->selectBySlug($user, $validated['package']);

        return redirect()
            ->to(url()->previous().'#platform-subscription')
            ->with('status', "You are now on the {$package->name} plan.");
    }
}
