# Database Logging

The package ships with an optional database logging feature that persists every SMS/WhatsApp API request and its response to a `fast2sms_logs` table.

## Publishing the Migration

```bash
php artisan vendor:publish --tag=fast2sms-migrations
```

Then run the migration:

```bash
php artisan migrate
```

## Table Structure

| Column          | Type      | Description                                      |
|-----------------|-----------|--------------------------------------------------|
| `id`            | bigint    | Auto-incrementing primary key                    |
| `request_id`    | string    | Unique identifier returned by the Fast2SMS API   |
| `payload`       | json      | The request payload sent to the API              |
| `response`      | json      | The raw response received from the API           |
| `is_success`    | boolean   | Whether the API call was successful              |
| `error_message` | string    | Error message if the call failed (nullable)      |
| `created_at`    | timestamp | When the log entry was created                   |
| `updated_at`    | timestamp | When the log entry was last updated              |

## Enabling Logging

Set `database_logging` to `true` in your `config/fast2sms.php`:

```php
'database_logging' => env('FAST2SMS_DATABASE_LOGGING', false),
```

Or via your `.env` file:

```env
FAST2SMS_DATABASE_LOGGING=true
```

## Querying Logs

```php
use Shakil\Fast2sms\Models\Fast2smsLog;

// All failed requests
Fast2smsLog::where('is_success', false)->get();

// Logs for a specific request ID
Fast2smsLog::where('request_id', 'abc123')->first();
```

> **Note:** Database logging is disabled by default to keep the package lightweight. Enable it only when you need an audit trail of API calls.
