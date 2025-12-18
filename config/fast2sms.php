<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Fast2sms API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key provided by Fast2sms. You can get this from your
    | Fast2sms dashboard under the 'Dev API' section.
    |
    */
    'api_key' => env('FAST2SMS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Fast2sms Default Sender ID
    |--------------------------------------------------------------------------
    |
    | This is your DLT approved Sender ID. It's a 3-6 letter ID.
    | This will be used as the default sender ID if not explicitly set.
    |
    */
    'default_sender_id' => env('FAST2SMS_DEFAULT_SENDER_ID', 'FSTSMS'),

    /*
    |--------------------------------------------------------------------------
    | Fast2sms Default Route
    |--------------------------------------------------------------------------
    |
    | This defines the default SMS route to use.
    | Options: 'dlt', 'otp', 'q' (Quick SMS).
    | 'dlt' is for DLT approved transactional/promotional SMS.
    | 'otp' is for OTP SMS.
    | 'q' is for Quick SMS (no DLT, random sender ID, higher cost).
    |
    */
    'default_route' => env('FAST2SMS_DEFAULT_ROUTE', 'dlt'),

    /*
    |--------------------------------------------------------------------------
    | Fast2sms API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Fast2sms API. Do not change this unless
    | Fast2sms updates their API endpoint.
    |
    */
    'base_url' => 'https://www.fast2sms.com/dev',

    /*
    |--------------------------------------------------------------------------
    | Fast2sms API Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a response from the Fast2sms API.
    |
    */
    'timeout' => 30,

    /**
     * ---------------------------------------------------------------------------
     * Fast2sms Balance Threshold
     * ---------------------------------------------------------------------------.
     *
     * This is the minimum balance threshold for triggering a low balance events.
     */
    'balance_threshold' => env('FAST2SMS_BALANCE_THRESHOLD', 1000),

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure the default queue settings for SMS jobs
    |
    */
    'queue' => [
        'connection' => env('FAST2SMS_QUEUE_CONNECTION', 'default'),
        'name' => env('FAST2SMS_QUEUE_NAME', 'fast2sms'),
    ],

];
