<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Generate named routes on the main (teacher/client) application URL.
 *
 * Admin requests force URL::root to the admin subdomain; emails and other
 * outbound links for teachers must use APP_URL instead.
 */
final class ClientUrl
{
    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $clientRoot = rtrim((string) config('app.url'), '/');
        $adminUrl = (string) config('app.admin_url');
        $adminHost = parse_url($adminUrl, PHP_URL_HOST);
        $onAdminHost = $adminHost && request()->getHost() === $adminHost;

        URL::forceRootUrl($clientRoot);

        try {
            return route($name, $parameters, $absolute);
        } finally {
            if ($onAdminHost) {
                URL::forceRootUrl($adminUrl);
            }
        }
    }
}
