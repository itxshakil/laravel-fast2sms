# Events & Listeners

Laravel Fast2SMS dispatches events at key points in the SMS and WhatsApp send lifecycle. You can listen to these events to add logging, alerting, analytics, or any custom behaviour.

---

## Available Events

| Event | Namespace | When |
|-------|-----------|------|
| `SmsSent` | `Shakil\Fast2sms\Events\SmsSent` | After a successful SMS send |
| `SmsFailed` | `Shakil\Fast2sms\Events\SmsFailed` | When an SMS send throws an exception |
| `WhatsAppSent` | `Shakil\Fast2sms\Events\WhatsAppSent` | After a successful WhatsApp send |
| `WhatsAppFailed` | `Shakil\Fast2sms\Events\WhatsAppFailed` | When a WhatsApp send throws an exception |
| `LowBalanceDetected` | `Shakil\Fast2sms\Events\LowBalanceDetected` | When wallet balance drops below threshold |

---

## Event Payloads

### `SmsSent`

```php
public function __construct(
    public array $payload,       // The SMS payload sent to the API
    public SmsResponse $response // The API response
) {}
```

### `SmsFailed`

```php
public function __construct(
    public array $payload,              // The SMS payload attempted
    public \Throwable $exception,       // The exception thrown
    public ?array $response = null      // Response if available
) {}
```

### `WhatsAppSent`

```php
public function __construct(
    public array $payload,            // The WhatsApp payload sent
    public WhatsAppResponse $response // The API response
) {}
```

### `WhatsAppFailed`

```php
public function __construct(
    public array $payload,              // The WhatsApp payload attempted
    public \Throwable $exception,       // The exception thrown
    public ?array $response = null      // Response if available
) {}
```

### `LowBalanceDetected`

```php
public function __construct(
    public float $balance,   // Current wallet balance
    public float $threshold  // Configured threshold
) {}
```

---

## Registering Listeners

Add your listeners in `App\Providers\EventServiceProvider`:

```php
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;

protected $listen = [
    SmsSent::class => [
        \App\Listeners\LogSmsSent::class,
        \App\Listeners\TrackSmsAnalytics::class,
    ],
    SmsFailed::class => [
        \App\Listeners\AlertOnSmsFailed::class,
    ],
    LowBalanceDetected::class => [
        \App\Listeners\NotifyAdminOfLowBalance::class,
    ],
    WhatsAppSent::class => [
        \App\Listeners\LogWhatsAppSent::class,
    ],
    WhatsAppFailed::class => [
        \App\Listeners\AlertOnWhatsAppFailed::class,
    ],
];
```

---

## Example Listener

```php
namespace App\Listeners;

use Shakil\Fast2sms\Events\SmsSent;

class LogSmsSent
{
    public function handle(SmsSent $event): void
    {
        \Log::info('SMS sent', [
            'request_id' => $event->response->requestId,
            'numbers'    => $event->payload['numbers'] ?? null,
        ]);
    }
}
```

---

## Disabling Events

To disable all event dispatching (e.g. in high-throughput scenarios):

```env
FAST2SMS_EVENTS_ENABLED=false
```

---

## Discover All Events via Artisan

```bash
php artisan fast2sms:events
```

This lists all package events with their descriptions in a table.

---

## Built-in Listeners

The package ships with built-in listeners that log to the database when `database_logging` is enabled:

| Listener | Event | Action |
|----------|-------|--------|
| `LogSmsSent` | `SmsSent` | Writes send record to DB |
| `LogSmsFailed` | `SmsFailed` | Writes failure record to DB |
| `LogWhatsAppSent` | `WhatsAppSent` | Writes send record to DB |
| `LogWhatsAppFailed` | `WhatsAppFailed` | Writes failure record to DB |

---

## See Also

- [Cost-Saving Features](cost-saving-features.md)
- [Error Handling](error-handling.md)
- [Configuration](configuration.md)
- [Testing](testing.md)
