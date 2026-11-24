<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SubscriptionSelectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! $user instanceof User) {
            return $next($request);
        }

        // Always re-read entitlement fields so admin plan grants apply immediately
        // in the student's next request (no stale in-memory attributes).
        $user->refresh();
        Auth::setUser($user);

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

        if ($this->isCatalogIndex($request)) {
            return $next($request);
        }

        $catalog = $this->catalogIndexRoute($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Choose a package to open this.',
            ], 402);
        }

        return redirect()
            ->route($catalog)
            ->with('open_packages', true);
    }

    /**
     * Listing pages stay open. Opening an item is what asks for a package.
     */
    private function isCatalogIndex(Request $request): bool
    {
        return in_array($request->route()?->getName(), [
            'client.vr-experiments.index',
            'client.models.3d.index',
            'client.projects.index',
            'client.vr-assessments.index',
            'client.weekly-sessions.index',
            'client.competitions.index',
            'client.my-students.index',
        ], true);
    }

    private function catalogIndexRoute(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return match (true) {
            str_starts_with($name, 'client.models.3d.') => 'client.models.3d.index',
            str_starts_with($name, 'client.projects.') => 'client.projects.index',
            str_starts_with($name, 'client.vr-assessments.') => 'client.vr-assessments.index',
            str_starts_with($name, 'client.weekly-sessions.') => 'client.weekly-sessions.index',
            str_starts_with($name, 'client.competitions.') => 'client.competitions.index',
            str_starts_with($name, 'client.my-students.') => 'client.my-students.index',
            default => 'client.vr-experiments.index',
        };
    }
}
