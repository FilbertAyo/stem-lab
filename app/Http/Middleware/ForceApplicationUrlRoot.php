<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceApplicationUrlRoot
{
    /**
     * Keep generated URLs on the current host (admin vs client).
     * Prevents admin subdomain root from leaking into client links.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminHost = parse_url((string) config('app.admin_url'), PHP_URL_HOST);
        $root = $request->getHost() === $adminHost
            ? (string) config('app.admin_url')
            : (string) config('app.url');

        URL::forceRootUrl(rtrim($root, '/'));

        return $next($request);
    }
}
