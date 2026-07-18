<?php

namespace App\Http\Middleware;

use App\Models\Model3d;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyModel3dUrls
{
    /**
     * Redirect GET/HEAD requests that resolved a Model3d via legacy ULID or numeric ID
     * to the canonical short public_id URL.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $model3d = $request->route('model3d');

        if (! $model3d instanceof Model3d) {
            return $next($request);
        }

        if (! $model3d->wasResolvedViaLegacyRouteKey()) {
            return $next($request);
        }

        if (blank($model3d->public_id)) {
            return $next($request);
        }

        $route = $request->route();
        if (! $route || ! $route->getName()) {
            return $next($request);
        }

        $parameters = $route->parameters();
        $parameters['model3d'] = $model3d;

        $url = route($route->getName(), $parameters);
        $queryString = $request->getQueryString();
        if ($queryString) {
            $url .= (str_contains($url, '?') ? '&' : '?').$queryString;
        }

        return redirect()->to($url, 301);
    }
}
