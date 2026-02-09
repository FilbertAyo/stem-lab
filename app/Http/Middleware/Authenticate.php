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

        // Check if the request is from admin subdomain or for an admin route
        $host = $request->getHost();
        $adminHost = parse_url(config('app.admin_url'), PHP_URL_HOST);
        
        if ($host === $adminHost || $request->is('admin*') || str_starts_with($request->route()?->getName() ?? '', 'admin.')) {
            return config('app.admin_url') . '/login';
        }

        // Default redirect for client routes
        return route('login');
    }
}
