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

    /*
    |--------------------------------------------------------------------------
    | Fast2sms Driver
    |--------------------------------------------------------------------------
    |
    | This defines the driver to use for sending SMS.
    | Options: 'api' (default), 'log'.
    | 'api' will hit the actual Fast2sms API.
    | 'log' will write the SMS content to your laravel.log file.
    |
    */
    'driver' => env('FAST2SMS_DRIVER', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Database Logging
    |--------------------------------------------------------------------------
    |
    | If set to true, the package will log all sent SMS messages to a
    | 'fast2sms_logs' database table.
    |
    */
    'database_logging' => env('FAST2SMS_DATABASE_LOGGING', false),

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure queued sending for SMS and WhatsApp jobs.
    |
    | enabled — Set to true to dispatch sending as queued jobs instead of
    | sending synchronously. Requires a queue worker to be running.
    | Example: FAST2SMS_QUEUE_ENABLED=true
    |
    | connection — The queue connection to use (e.g. 'redis', 'database').
    | Defaults to the application's default queue connection.
    | Example: FAST2SMS_QUEUE_CONNECTION=redis
    |
    | name — The queue name to push jobs onto.
    | Example: FAST2SMS_QUEUE_NAME=sms
    |
    | tries — Maximum number of attempts before a job is marked as failed.
    | Example: FAST2SMS_QUEUE_TRIES=3
    |
    */
    'queue' => [
        'enabled' => env('FAST2SMS_QUEUE_ENABLED', false),
        'connection' => env('FAST2SMS_QUEUE_CONNECTION'),
        'name' => env('FAST2SMS_QUEUE_NAME', 'default'),
        'tries' => env('FAST2SMS_QUEUE_TRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Settings
    |--------------------------------------------------------------------------
    |
    | Control whether the package dispatches Laravel events (SmsSent, SmsFailed,
    | WhatsAppSent, WhatsAppFailed, LowBalanceDetected).
    |
    | Set FAST2SMS_EVENTS_ENABLED=false to disable all event dispatching,
    | for example, in high-throughput environments where listeners are not needed.
    |
    */
    'events' => [
        'enabled' => env('FAST2SMS_EVENTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost-Saving Features
    |--------------------------------------------------------------------------
    |
    | These opt-in features reduce wasted API credits. All are disabled by
    | default and can be enabled via environment variables.
    |
    */

    /*
     * Deduplication / Idempotency Guard
     *
     * When enabled, a hash of (recipients + message + route) is stored in the
     * cache for `ttl` seconds. A second identical call within that window throws
     * DuplicateSendException instead of hitting the API again.
     */
    'deduplication' => [
        'enabled' => env('FAST2SMS_DEDUP_ENABLED', false),
        'ttl' => env('FAST2SMS_DEDUP_TTL', 60),
        'store' => env('FAST2SMS_DEDUP_STORE', null),
    ],

    /*
     * Pre-send Recipient Validation
     *
     * When enabled, each recipient is validated against the Fast2smsPhone rule
     * before the API call. Invalid numbers are stripped and a warning is logged.
     * If all numbers are invalid, ValidationException::allRecipientsInvalid() is thrown.
     */
    'validation' => [
        'strip_invalid_recipients' => env('FAST2SMS_STRIP_INVALID', false),
    ],

    /*
     * Recipient List Deduplication & Batch Splitting
     *
     * `deduplicate` removes duplicate numbers from the recipient list before sending.
     * `batch_size` splits large lists into chunks of that size (0 = no splitting).
     */
    'recipients' => [
        'deduplicate' => env('FAST2SMS_DEDUP_RECIPIENTS', true),
        'batch_size' => env('FAST2SMS_BATCH_SIZE', 0),
    ],

    /*
     * Balance Gate
     *
     * When enabled, the wallet balance is checked before every send. If the balance
     * is below `threshold`, a LowBalanceDetected event is fired. When `abort` is
     * true, InsufficientBalanceException is also thrown to prevent the API call.
     */
    'balance_gate' => [
        'enabled' => env('FAST2SMS_BALANCE_GATE', false),
        'threshold' => env('FAST2SMS_BALANCE_THRESHOLD', 10.0),
        'abort' => env('FAST2SMS_BALANCE_ABORT', true),
    ],

    /*
     * Send-Rate Throttle
     *
     * When enabled, a per-minute sliding-window counter is maintained in the cache.
     * If the counter reaches `max_per_minute`, ThrottleExceededException is thrown.
     */
    'throttle' => [
        'enabled' => env('FAST2SMS_THROTTLE_ENABLED', false),
        'max_per_minute' => env('FAST2SMS_THROTTLE_MAX', 60),
        'store' => env('FAST2SMS_THROTTLE_STORE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Settings
    |--------------------------------------------------------------------------
    |
    | Configure the WhatsApp settings for sending WhatsApp messages.
    |
    */
    'whatsapp' => [
        'default_phone_number_id' => env('FAST2SMS_WHATSAPP_PHONE_NUMBER_ID', ''),
        'default_waba_id' => env('FAST2SMS_WHATSAPP_WABA_ID', ''),
        'version' => env('FAST2SMS_WHATSAPP_VERSION', 'v24.0'),
    ],

];
