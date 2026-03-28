# Queuing

Laravel Fast2SMS supports dispatching SMS and WhatsApp sends as background jobs, keeping your HTTP responses fast.

---

## Setup

### 1. Enable Queuing

```env
FAST2SMS_QUEUE_ENABLED=true
```

### 2. Configure Queue (optional)

```env
FAST2SMS_QUEUE_CONNECTION=redis
FAST2SMS_QUEUE_NAME=sms
FAST2SMS_QUEUE_TRIES=3
```

### 3. Run Your Queue Worker

```bash
php artisan queue:work --queue=sms
```

---

## How It Works

When `queue.enabled` is `true`, every call to `quick()`, `otp()`, `dlt()` or WhatsApp send methods automatically dispatches a `SendSmsJob` or `SendWhatsAppJob` instead of making a synchronous HTTP request.

```php
use Shakil\Fast2sms\Facades\Fast2sms;

// This dispatches a job when queue.enabled=true
Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
```

---

## Per-Send Queue Overrides

Override queue settings for a single send without changing the global config:

```php
Fast2sms::onQueue('high-priority')
    ->onConnection('redis')
    ->quick(numbers: '9876543210', message: 'Urgent message!');
```

> **Note:** `onQueue()` and `onConnection()` throw a `ConfigurationException` if `queue.enabled` is `false`.

---

## Job Classes

The package provides two job classes:

| Job | Handles |
|-----|---------|
| `Shakil\Fast2sms\Jobs\SendSmsJob` | SMS sends |
| `Shakil\Fast2sms\Jobs\SendWhatsAppJob` | WhatsApp sends |

Both implement `ShouldQueue` and use the `Dispatchable`, `InteractsWithQueue`, `Queueable`, and `SerializesModels` traits.

---

## Retry Strategy

Configure the number of attempts via `queue.tries`:

```env
FAST2SMS_QUEUE_TRIES=3
```

The package retries on network errors and 5xx responses. It does **not** retry on 4xx errors (authentication failures, validation errors) since retrying would not help.

---

## Failed Jobs

If all retry attempts are exhausted, the job is moved to the failed jobs table. You can inspect and retry failed jobs with:

```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## Testing with Queues

When using `Fast2sms::fake()`, jobs are **not** dispatched — the fake intercepts calls synchronously. This means your assertions work without needing a queue worker in tests.

```php
Fast2sms::fake();

Fast2sms::quick(numbers: '9876543210', message: 'Hello!');

Fast2sms::assertSmsSent(); // Works even with queue.enabled=true
```

---

## See Also

- [Cost-Saving Features](cost-saving-features.md)
- [Configuration](configuration.md)
- [Testing](testing.md)
- [Error Handling](error-handling.md)
