<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClubLinkedForLearning
{
    /**
     * Block unlinked public-school students from learning routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'student') {
            return $next($request);
        }

        if ($user->subscription_tier === 'public_school' && ! $user->isClubLinked()) {
            return redirect()
                ->route('client.student-club.index')
                ->with('warning', 'Link your club account to access learning content for free.');
        }

        return $next($request);
    }
}
