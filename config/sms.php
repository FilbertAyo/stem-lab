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

    /*
    |--------------------------------------------------------------------------
    | Segment billing
    |--------------------------------------------------------------------------
    |
    | Soscom deducts one SMS credit per segment per recipient. Gsm-7 single
    | messages hold 160 septets and concatenated parts hold 153. Unicode
    | (UCS-2) holds 70, or 67 when concatenated. SmsCreditCalculator is the
    | only place these limits are applied; the compose preview and the send
    | path both call it.
    |
    */

    'segments' => [
        'gsm_single' => (int) env('SMS_GSM_SINGLE', 160),
        'gsm_concat' => (int) env('SMS_GSM_CONCAT', 153),
        'unicode_single' => (int) env('SMS_UNICODE_SINGLE', 70),
        'unicode_concat' => (int) env('SMS_UNICODE_CONCAT', 67),
    ],

];
