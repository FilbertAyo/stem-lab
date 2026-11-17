<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Soscom SMS
    |--------------------------------------------------------------------------
    |
    | Tanzanian SMS via https://sms.soscom.co.tz/api/v1
    |
    | TEMPORARY: live API key is hardcoded as a fallback so production can send
    | without editing .env. Move the key back to SOSCOM_SMS_API_KEY and remove
    | the fallback as soon as production env is updated.
    |
    */

    'enabled' => (bool) env('SOSCOM_SMS_ENABLED', true),

    'base_url' => rtrim((string) env('SOSCOM_SMS_BASE_URL', 'https://sms.soscom.co.tz/api/v1'), '/'),

    // Prefer .env; fall back to temporary hardcoded key for urgent production sends.
    'api_key' => env('SOSCOM_SMS_API_KEY') ?: 'sk_live_7be8a22e54ecd5b84e8accaf9d96c7660ca11c6d93005f26',

    'default_sender' => env('SOSCOM_SMS_DEFAULT_SENDER', 'ADILISHA'),

    'allowed_senders' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SOSCOM_SMS_ALLOWED_SENDERS', 'ADILISHA,ADILISHAXR,STEMLABS'))
    ))),

    'timeout' => (int) env('SOSCOM_SMS_TIMEOUT', 15),

];
