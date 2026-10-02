<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Absolute links on the admin host, including when the current request is the public site.
 */
final class AdminUrl
{
    public static function route(string $name, mixed $parameters = []): string
    {
        $adminRoot = rtrim((string) config('app.admin_url'), '/');
        $clientRoot = rtrim((string) config('app.url'), '/');
        $adminHost = parse_url($adminRoot, PHP_URL_HOST);
        $onAdminHost = $adminHost && request()->getHost() === $adminHost;

        URL::forceRootUrl($adminRoot);

        try {
            return route($name, $parameters, true);
        } finally {
            URL::forceRootUrl($onAdminHost ? $adminRoot : $clientRoot);
        }
    }
}
