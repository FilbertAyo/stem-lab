<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect(config('app.admin_url') . '/login');
        }

        // Refresh user from database to ensure we have the latest is_active status
        $user->refresh();

        if ($user->role !== 'admin') {
            abort(403);
        }

        // Check if admin user is active - if disabled, log them out immediately
        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect(config('app.admin_url') . '/login')
                ->with('error', 'Your account has been disabled. Please contact an administrator.');
        }

        return $next($request);
    }
}

