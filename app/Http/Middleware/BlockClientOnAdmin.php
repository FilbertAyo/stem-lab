<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockClientOnAdmin
{
    /**
     * Handle an incoming request.
     * Block client auth routes when accessed from admin subdomain.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $adminUrl = config('app.admin_url', 'https://admin.adilishastemlabs.com');
        $adminHost = parse_url($adminUrl, PHP_URL_HOST);
        
        if ($host === $adminHost) {
            // If on admin subdomain, redirect to admin login
            return redirect($adminUrl . '/login');
        }
        
        return $next($request);
    }
}
