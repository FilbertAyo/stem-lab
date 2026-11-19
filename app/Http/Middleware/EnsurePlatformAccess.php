<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionSelectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAccess
{
    public function __construct(
        private readonly SubscriptionSelectionService $selectionService,
    ) {}

    /**
     * When payments are enabled, require subsidised club access or an active prepaid plan.
     * Always keep the existing public-school club-link gate for students.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->role === 'student'
            && $user->subscription_tier === 'public_school'
            && ! $user->isClubLinked()
        ) {
            return redirect()
                ->route('client.student-club.index')
                ->with('warning', 'Link your club account to access learning content for free.');
        }

        if (! $this->selectionService->entitlementsEnforced()) {
            return $next($request);
        }

        if ($user->hasPlatformAccess()) {
            return $next($request);
        }

        return redirect()
            ->route('client.billing.index', ['paywall' => 1])
            ->with('warning', 'Subscribe or join a public-school STEM club to continue.');
    }
}
