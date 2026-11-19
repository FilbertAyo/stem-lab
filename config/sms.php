<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Soscom SMS
    |--------------------------------------------------------------------------
    |
    | Tanzanian SMS via https://sms.soscom.co.tz/api/v1
    | Set SOSCOM_SMS_API_KEY in .env only — never commit live keys.
    |
    */

    'enabled' => (bool) env('SOSCOM_SMS_ENABLED', true),

    'base_url' => rtrim((string) env('SOSCOM_SMS_BASE_URL', 'https://sms.soscom.co.tz/api/v1'), '/'),

    'api_key' => env('SOSCOM_SMS_API_KEY'),

    'default_sender' => env('SOSCOM_SMS_DEFAULT_SENDER', 'ADILISHA'),

    'allowed_senders' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SOSCOM_SMS_ALLOWED_SENDERS', 'ADILISHA,ADILISHAXR,STEMLABS'))
    ))),

    'timeout' => (int) env('SOSCOM_SMS_TIMEOUT', 15),

];
