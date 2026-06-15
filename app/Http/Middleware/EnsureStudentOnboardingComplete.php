<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentOnboardingComplete
{
    /**
     * Redirect students who have not finished onboarding away from the main app.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $user->role === 'student'
            && $user->needsStudentOnboarding()
            && ! $request->routeIs('student.onboarding.*')
            && ! $request->routeIs('logout')) {
            return redirect()->route('student.onboarding.show');
        }

        return $next($request);
    }
}
