<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription payments
    |--------------------------------------------------------------------------
    |
    | When false, free-trial and individual package buttons stay disabled and
    | entitlement enforcement remains off (legacy open access for non-club users).
    | When true, Snippe checkout is available and learning routes require a
    | valid free-club or prepaid entitlement.
    |
    */
    'payments_enabled' => env('SUBSCRIPTION_PAYMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Renewal reminders
    |--------------------------------------------------------------------------
    */
    'renewal_reminder_days' => (int) env('SUBSCRIPTION_RENEWAL_REMINDER_DAYS', 3),
];
