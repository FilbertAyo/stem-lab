<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Check if the request is for an admin route
        // Check both path prefix and route name prefix
        if ($request->is('admin*') || str_starts_with($request->route()?->getName() ?? '', 'admin.')) {
            return route('admin.login');
        }

        // Default redirect for client routes
        return route('login');
    }
}
