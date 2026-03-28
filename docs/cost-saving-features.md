# Cost-Saving Features

Laravel Fast2SMS v2.0 ships with six opt-in features that help you avoid unnecessary API calls, prevent accidental duplicate sends, and keep your SMS spend under control. All features are **disabled by default** — enable only what you need.

---

## Quick-Reference Table

| Feature | Config key | Env variable(s) | Default | Exception thrown |
|---------|-----------|-----------------|---------|-----------------|
| Recipient deduplication | `fast2sms.recipients.deduplicate` | `FAST2SMS_DEDUP_RECIPIENTS` | `true` | — |
| Invalid recipient stripping | `fast2sms.validation.strip_invalid_recipients` | `FAST2SMS_STRIP_INVALID` | `false` | `ValidationException` (all invalid) |
| Idempotency / dedup guard | `fast2sms.deduplication.enabled` | `FAST2SMS_DEDUP_ENABLED` | `false` | `DuplicateSendException` |
| Send-rate throttle | `fast2sms.throttle.enabled` | `FAST2SMS_THROTTLE_ENABLED` | `false` | `ThrottleExceededException` |
| Balance gate | `fast2sms.balance_gate.enabled` | `FAST2SMS_BALANCE_GATE` | `false` | `InsufficientBalanceException` |
| Batch splitting | `fast2sms.recipients.batch_size` | `FAST2SMS_BATCH_SIZE` | `0` (off) | — |

---

## 1. Recipient Deduplication

Strips duplicate phone numbers from the recipient list **before** every SMS send. Enabled by default.

### Config

```php
// config/fast2sms.php
'recipients' => [
    'deduplicate' => env('FAST2SMS_DEDUP_RECIPIENTS', true),
],
```

### .env

```dotenv
FAST2SMS_DEDUP_RECIPIENTS=true
```

### Behaviour

If you pass `['9876543210', '9876543210', '9123456789']`, only `['9876543210', '9123456789']` will be sent to the API.

---

## 2. Invalid Recipient Stripping

Validates each number against the `Fast2smsPhone` rule before sending. Invalid numbers are silently removed and a warning is logged. If **all** numbers are invalid, a `ValidationException` is thrown.

### Config

```php
'validation' => [
    'strip_invalid_recipients' => env('FAST2SMS_STRIP_INVALID', false),
],
```

### .env

```dotenv
FAST2SMS_STRIP_INVALID=true
```

### Exception

```php
use Shakil\Fast2sms\Exceptions\ValidationException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (ValidationException $e) {
    // All recipients were invalid — nothing was sent
}
```

---

## 3. Idempotency / Dedup Guard

Caches a hash of `(recipients + message + route)` for a configurable TTL. A second identical call within that window throws `DuplicateSendException` instead of hitting the API again. Also applied to WhatsApp sends.

### Config

```php
'deduplication' => [
    'enabled' => env('FAST2SMS_DEDUP_ENABLED', false),
    'ttl'     => env('FAST2SMS_DEDUP_TTL', 60),   // seconds
    'store'   => env('FAST2SMS_DEDUP_STORE', null), // null = default cache store
],
```

### .env

```dotenv
FAST2SMS_DEDUP_ENABLED=true
FAST2SMS_DEDUP_TTL=120
FAST2SMS_DEDUP_STORE=redis
```

### Exception

```php
use Shakil\Fast2sms\Exceptions\DuplicateSendException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (DuplicateSendException $e) {
    // Identical send attempted within the dedup window
}
```

---

## 4. Send-Rate Throttle

Sliding-window per-minute counter backed by the Laravel cache. Throws `ThrottleExceededException` when the configured limit is reached. Also applied to WhatsApp sends.

### Config

```php
'throttle' => [
    'enabled'        => env('FAST2SMS_THROTTLE_ENABLED', false),
    'max_per_minute' => env('FAST2SMS_THROTTLE_MAX', 60),
    'store'          => env('FAST2SMS_THROTTLE_STORE', null),
],
```

### .env

```dotenv
FAST2SMS_THROTTLE_ENABLED=true
FAST2SMS_THROTTLE_MAX=30
FAST2SMS_THROTTLE_STORE=redis
```

### Exception

```php
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (ThrottleExceededException $e) {
    // Too many sends in the current minute
}
```

---

## 5. Balance Gate

Checks your Fast2SMS wallet balance before every send. Fires the `LowBalanceDetected` event and — when `abort` is `true` — throws `InsufficientBalanceException` if the balance is below the threshold. Also applied to WhatsApp sends.

### Config

```php
'balance_gate' => [
    'enabled'   => env('FAST2SMS_BALANCE_GATE', false),
    'threshold' => env('FAST2SMS_BALANCE_THRESHOLD', 10.0),
    'abort'     => env('FAST2SMS_BALANCE_ABORT', true),
],
```

### .env

```dotenv
FAST2SMS_BALANCE_GATE=true
FAST2SMS_BALANCE_THRESHOLD=25.0
FAST2SMS_BALANCE_ABORT=true
```

### Exception

```php
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (InsufficientBalanceException $e) {
    // Wallet balance is below the configured threshold
}
```

### Event-only mode

Set `FAST2SMS_BALANCE_ABORT=false` to fire `LowBalanceDetected` as a warning without blocking the send:

```dotenv
FAST2SMS_BALANCE_GATE=true
FAST2SMS_BALANCE_ABORT=false
```

---

## 6. Batch Splitting

Splits large recipient lists into chunks and issues one API call per chunk. Set `batch_size` to `0` to disable (all recipients in a single call).

### Config

```php
'recipients' => [
    'batch_size' => env('FAST2SMS_BATCH_SIZE', 0),
],
```

### .env

```dotenv
FAST2SMS_BATCH_SIZE=50
```

### Behaviour

With `batch_size=50` and 120 recipients, the package will make three API calls: 50 + 50 + 20.

---

## SmsMessage Credit Helpers

`SmsMessage` exposes helper methods for pre-send cost estimation:

```php
$msg = SmsMessage::create('Hello! Your OTP is 123456.');

$msg->charCount();      // int — number of characters
$msg->isUnicode();      // bool — true if message contains non-GSM characters
$msg->creditCount();    // int — estimated SMS credits consumed
$msg->exceedsOneSms();  // bool — true if message spans more than one SMS part
```

---

## Recommended .env for Production

```dotenv
# Recipient safety
FAST2SMS_DEDUP_RECIPIENTS=true
FAST2SMS_STRIP_INVALID=true

# Idempotency (requires a persistent cache store)
FAST2SMS_DEDUP_ENABLED=true
FAST2SMS_DEDUP_TTL=60
FAST2SMS_DEDUP_STORE=redis

# Rate limiting
FAST2SMS_THROTTLE_ENABLED=true
FAST2SMS_THROTTLE_MAX=60
FAST2SMS_THROTTLE_STORE=redis

# Balance protection
FAST2SMS_BALANCE_GATE=true
FAST2SMS_BALANCE_THRESHOLD=10.0
FAST2SMS_BALANCE_ABORT=true

# Batch splitting (0 = disabled)
FAST2SMS_BATCH_SIZE=0
```

---

## See Also

- [Configuration](configuration.md) — full config reference
- [Error Handling](error-handling.md) — all exception classes
- [Testing](testing.md) — how to test with cost-saving features enabled
- [CHANGELOG.md](../CHANGELOG.md) — what changed in v2.0
