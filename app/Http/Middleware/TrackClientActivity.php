<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackClientActivity
{
    /**
     * Log authenticated client (main-domain) route activity using Spatie activitylog.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (! $user || $user->role === 'admin') {
            return $response;
        }

        $route = $request->route();

        activity('client_activity')
            ->causedBy($user)
            ->event(strtoupper($request->method()))
            ->withProperties([
                'user_role' => $user->role,
                'user_id' => $user->id,
                'route_name' => $route?->getName(),
                'uri' => $request->path(),
                'full_url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'query' => $request->query(),
                'payload' => $request->except([
                    '_token',
                    '_method',
                    'password',
                    'password_confirmation',
                    'current_password',
                ]),
            ])
            ->log('Client activity on blade-backed route');

        return $response;
    }
}
