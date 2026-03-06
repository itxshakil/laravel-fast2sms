# Error Handling

Laravel Fast2SMS uses a typed exception hierarchy so you can handle errors precisely or broadly.

---

## Exception Hierarchy

```
\Throwable
└── \Exception
    └── Fast2smsException
        ├── ConfigurationException       — invalid config
        ├── ValidationException          — invalid input
        ├── NetworkException             — connection/timeout failure
        ├── DuplicateSendException       — duplicate send blocked by deduplication guard
        ├── InsufficientBalanceException — wallet balance below configured threshold
        ├── ThrottleExceededException    — send rate limit exceeded
        └── ApiException                 — API returned an error
            ├── AuthenticationException  (HTTP 401)
            ├── RateLimitException       (HTTP 429)
            └── ServerException          (HTTP 5xx)
```

All package exceptions extend `Fast2smsException`, so a single catch block can handle everything if needed.

---

## Exception Reference

| Exception | When | Key Properties |
|-----------|------|----------------|
| `ConfigurationException` | Missing API key, invalid driver, bad timeout | `getMessage()` |
| `ValidationException` | Empty numbers, invalid phone format, missing message | `getMessage()` |
| `NetworkException` | Connection timeout, DNS failure | `getPrevious()` |
| `DuplicateSendException` | Same message sent to same numbers within the deduplication TTL | `getMessage()` |
| `InsufficientBalanceException` | Wallet balance is below `balance_gate.threshold` | `getMessage()` |
| `ThrottleExceededException` | More than `throttle.max_per_minute` sends in the sliding window | `getMessage()` |
| `ApiException` | Non-2xx API response (catch-all) | `getMessage()` |
| `AuthenticationException` | HTTP 401 — invalid/missing API key | `getMessage()` |
| `RateLimitException` | HTTP 429 — too many requests | `getMessage()` |
| `ServerException` | HTTP 5xx — Fast2SMS server error | `getMessage()` |

---

## Catching Exceptions

### Catch All Package Exceptions

```php
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (Fast2smsException $e) {
    \Log::error('Fast2SMS error: ' . $e->getMessage());
}
```

### Catch Specific Exceptions

```php
use Shakil\Fast2sms\Exceptions\ApiException;
use Shakil\Fast2sms\Exceptions\AuthenticationException;
use Shakil\Fast2sms\Exceptions\ConfigurationException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\NetworkException;
use Shakil\Fast2sms\Exceptions\RateLimitException;
use Shakil\Fast2sms\Exceptions\ValidationException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (AuthenticationException $e) {
    // Invalid API key — check FAST2SMS_API_KEY in .env
    abort(500, 'SMS service misconfigured.');
} catch (RateLimitException $e) {
    // Too many requests — back off and retry later
    \Log::warning('Fast2SMS rate limit hit.');
} catch (ValidationException $e) {
    // Bad input — safe to show to developers
    throw $e;
} catch (NetworkException $e) {
    // Transient network issue — consider retrying
    \Log::error('Fast2SMS network error', ['error' => $e->getMessage()]);
} catch (ApiException $e) {
    // Other API error
    \Log::error('Fast2SMS API error', ['error' => $e->getMessage()]);
} catch (Fast2smsException $e) {
    // Catch-all
    \Log::error('Fast2SMS error', ['error' => $e->getMessage()]);
}
```

---

## Cost-Saving Guards

When the [cost-saving features](cost-saving-features.md) are enabled, three additional exceptions may be thrown **before** any HTTP call is made:

### `DuplicateSendException`

Thrown when the same message payload has already been sent to the same recipients within the configured deduplication TTL.

```php
use Shakil\Fast2sms\Exceptions\DuplicateSendException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (DuplicateSendException $e) {
    // Duplicate detected — skip or log, do not retry immediately
    \Log::info('Duplicate SMS blocked: ' . $e->getMessage());
}
```

Enable via `FAST2SMS_DEDUP_ENABLED=true` in `.env`. See [Cost-Saving Features](cost-saving-features.md#1-deduplicationidempotency-guard).

### `InsufficientBalanceException`

Thrown when the wallet balance is below the configured `balance_gate.threshold` before a send attempt.

```php
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (InsufficientBalanceException $e) {
    // Top up the Fast2SMS wallet before retrying
    \Log::critical('Insufficient Fast2SMS balance: ' . $e->getMessage());
}
```

Enable via `FAST2SMS_BALANCE_GATE=true` and `FAST2SMS_BALANCE_THRESHOLD=10` in `.env`. See [Cost-Saving Features](cost-saving-features.md#5-balance-gate).

### `ThrottleExceededException`

Thrown when the number of send calls exceeds `throttle.max_per_minute` within the sliding window.

```php
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (ThrottleExceededException $e) {
    // Back off and retry after a minute
    \Log::warning('Fast2SMS throttle exceeded: ' . $e->getMessage());
}
```

Enable via `FAST2SMS_THROTTLE_ENABLED=true` and `FAST2SMS_THROTTLE_MAX=60` in `.env`. See [Cost-Saving Features](cost-saving-features.md#4-send-rate-throttling).

---

## Configuration Errors

`ConfigurationException` is thrown before any HTTP call is made, when the config is invalid:

```php
use Shakil\Fast2sms\Exceptions\ConfigurationException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (ConfigurationException $e) {
    // Check: FAST2SMS_API_KEY is set, FAST2SMS_DRIVER is 'api' or 'log'
}
```

---

## Validation Errors

`ValidationException` is thrown when input fails validation before the API call:

```php
use Shakil\Fast2sms\Exceptions\ValidationException;

try {
    Fast2sms::quick(numbers: '', message: '');
} catch (ValidationException $e) {
    echo $e->getMessage(); // "Phone number cannot be empty"
}
```

---

## Logging Errors via Events

Rather than wrapping every call in try/catch, listen to the `SmsFailed` and `WhatsAppFailed` events:

```php
// In EventServiceProvider
use Shakil\Fast2sms\Events\SmsFailed;

protected $listen = [
    SmsFailed::class => [
        \App\Listeners\AlertOnSmsFailed::class,
    ],
];
```

```php
// App\Listeners\AlertOnSmsFailed
public function handle(SmsFailed $event): void
{
    \Log::error('SMS failed', [
        'exception' => $event->exception->getMessage(),
        'payload'   => $event->payload,
    ]);
}
```

---

## See Also

- [Events & Listeners](events.md)
- [Configuration](configuration.md)
- [Testing](testing.md)
- [Cost-Saving Features](cost-saving-features.md)
