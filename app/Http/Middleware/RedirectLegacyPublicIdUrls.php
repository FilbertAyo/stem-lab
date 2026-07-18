<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyPublicIdUrls
{
    /**
     * Redirect GET/HEAD requests that resolved a short-ID model via legacy ULID or numeric ID
     * to the canonical short public_id URL.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $route = $request->route();
        if (! $route || ! $route->getName()) {
            return $next($request);
        }

        $parameters = $route->parameters();
        $needsRedirect = false;

        foreach ($parameters as $key => $parameter) {
            if (! $parameter instanceof Model) {
                continue;
            }

            if (! method_exists($parameter, 'wasResolvedViaLegacyRouteKey')) {
                continue;
            }

            if (! $parameter->wasResolvedViaLegacyRouteKey()) {
                continue;
            }

            if (blank($parameter->getAttribute('public_id'))) {
                continue;
            }

            $parameters[$key] = $parameter;
            $needsRedirect = true;
        }

        if (! $needsRedirect) {
            return $next($request);
        }

        $url = route($route->getName(), $parameters);
        $queryString = $request->getQueryString();
        if ($queryString) {
            $url .= (str_contains($url, '?') ? '&' : '?').$queryString;
        }

        return redirect()->to($url, 301);
    }
}
