<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Snippe API
    |--------------------------------------------------------------------------
    |
    | Hosted Payment Sessions collect mobile-money payments (M-Pesa, Airtel,
    | Mixx by Yas, Halotel). Keep secrets server-side only.
    |
    */

    'api_key' => env('SNIPPE_API_KEY'),

    'webhook_secret' => env('SNIPPE_WEBHOOK_SECRET'),

    'base_url' => env('SNIPPE_BASE_URL', 'https://api.snippe.sh'),

    'profile_id' => env('SNIPPE_PROFILE_ID'),

    'currency' => env('SNIPPE_CURRENCY', 'TZS'),

    'session_expires_in' => (int) env('SNIPPE_SESSION_EXPIRES_IN', 3600),

    'webhook_tolerance_seconds' => (int) env('SNIPPE_WEBHOOK_TOLERANCE_SECONDS', 300),

    'http_timeout' => (int) env('SNIPPE_HTTP_TIMEOUT', 30),
];
