# Upgrading Guide

## Upgrading from v1.x to v2.0.0

This guide covers all breaking changes introduced in v2.0.0 and explains how to migrate your application.

---

## Step-by-step Migration

### 1. Update `composer.json`

```bash
composer require itxshakil/laravel-fast2sms:^2.0
```

### 2. Update Exception Catches

`ConfigValidator::validate()` previously threw `\InvalidArgumentException`. It now throws `Shakil\Fast2sms\Exceptions\ConfigurationException`.

**Before:**
```php
try {
    // ...
} catch (\InvalidArgumentException $e) {
    // handle config error
}
```

**After:**
```php
use Shakil\Fast2sms\Exceptions\ConfigurationException;

try {
    // ...
} catch (ConfigurationException $e) {
    // handle config error
}
```

For finer-grained error handling, v2 introduces a full exception hierarchy:

| Exception | When thrown |
|-----------|-------------|
| `ConfigurationException` | Invalid or missing config values |
| `ValidationException` | Invalid message parameters |
| `AuthenticationException` | HTTP 401 from the API |
| `RateLimitException` | HTTP 429 from the API |
| `ApiException` | Other HTTP 4xx/5xx errors |
| `NetworkException` | Connection/timeout failures |
| `ServerException` | HTTP 5xx errors |

All exceptions extend `Fast2smsException` for backward-compatible catch-all blocks.

---

### 3. Update DTO Usage

DTOs (`Fast2smsConfig`, `SmsParameters`, `WhatsAppParameters`) are now `readonly class`. You cannot mutate properties after construction.

**Before:**
```php
$params = new SmsParameters(...);
$params->message = 'new message'; // ❌ no longer works
```

**After:**
```php
// Reconstruct the DTO with the new value
$params = new SmsParameters(
    numbers: $params->numbers,
    message: 'new message',
    // ...
);
```

---

### 4. Update Fake Assertions

`Fast2smsFake` recorded calls are now typed value objects instead of raw arrays. The old `$fake->recorded` property no longer exists.

**Before:**
```php
Fast2sms::fake();
// ...
$recorded = Fast2sms::sentMessages(); // returned raw arrays
$this->assertEquals('Hello', $recorded[0]['message']);
```

**After:**
```php
Fast2sms::fake();
// ...
Fast2sms::assertSmsSent();
Fast2sms::assertSmsSentWithMessage('Hello');
Fast2sms::assertSmsSentTo('9876543210');
Fast2sms::assertSmsSentCount(1);
Fast2sms::assertNothingSent();
Fast2sms::assertSentTimes(1); // counts raw sentMessages (all payloads); assertSentCount() counts typed recordedSms + recordedWhatsApp

// Access typed records directly
$sent = Fast2sms::sentSms(); // returns RecordedSmsSend[]
$this->assertEquals('Hello', $sent[0]->parameters->message);
```

> **Note:** `assertSent(array|Closure|null $callback = null)` and `assertNotSent(array|Closure|null $callback = null)` still exist in v2 as low-level generic cross-channel methods. The typed channel-specific variants (`assertSmsSent()`, `assertWhatsAppSent()`, etc.) are preferred for clarity.

---

### 5. Update Notification Routing Methods

`SmsChannel` and `WhatsAppChannel` now throw `\LogicException` when the notifiable model is missing the required routing method, instead of silently failing.

Ensure your notifiable models implement the routing methods:

```php
class User extends Authenticatable
{
    // For SMS notifications
    public function routeNotificationForFast2sms(): string
    {
        return $this->phone_number;
    }

    // For WhatsApp notifications
    public function routeNotificationForWhatsapp(): string
    {
        return $this->whatsapp_number;
    }
}
```

---

### 6. Update Deprecated Method Calls

The following `SmsMessage` methods have been renamed. The old names still work in v2.0.0 but emit `E_USER_DEPRECATED` notices and **will be removed in v3.0.0**.

| Deprecated (v1) | Replacement (v2) |
|-----------------|-----------------|
| `SmsMessage::content(string $msg)` | `SmsMessage::withContent(string $msg)` |
| `SmsMessage::route(SmsRoute $r)` | `SmsMessage::withRoute(SmsRoute $r)` |
| `SmsMessage::to(string\|array $n)` | `SmsMessage::withNumbers(string\|array $n)` |

**Before:**
```php
$message = (new SmsMessage)
    ->content('Hello World')
    ->route(SmsRoute::QUICK)
    ->to('9876543210');
```

**After:**
```php
$message = (new SmsMessage)
    ->withContent('Hello World')
    ->withRoute(SmsRoute::QUICK)
    ->withNumbers('9876543210');

// Or use the named constructor:
$message = SmsMessage::create('Hello World')
    ->withRoute(SmsRoute::QUICK)
    ->withNumbers('9876543210');
```

---

### 7. Update Type-hints on Response Objects

All API methods now return `ResponseInterface` instead of the concrete `Fast2smsResponse`.

**Before:**
```php
use Shakil\Fast2sms\Responses\Fast2smsResponse;

public function sendSms(): Fast2smsResponse
{
    return Fast2sms::quick('Hello', '9876543210');
}
```

**After:**
```php
use Shakil\Fast2sms\Contracts\ResponseInterface;

public function sendSms(): ResponseInterface
{
    return Fast2sms::quick('Hello', '9876543210');
}
```

---

## Full Breaking Changes Reference

| Change | Impact | Migration |
|--------|--------|-----------|
| `Fast2smsException` is now `final` | Cannot be extended | Extend a specific subtype instead |
| DTOs are `readonly class` | Properties cannot be mutated after construction | Reconstruct the DTO |
| `ConfigValidator::validate()` throws `ConfigurationException` | `catch (\InvalidArgumentException)` blocks break | Change to `catch (ConfigurationException)` |
| `ResponseFactory` throws `ApiException` on unknown type | Code relying on `UnhandledMatchError` breaks | Catch `ApiException` |
| `Fast2smsFake` recorded calls are typed objects | Code accessing `$fake->recorded` directly breaks | Use `sentSms()` / `sentWhatsApp()` accessors |
| `SmsChannel`/`WhatsAppChannel` throw `\LogicException` on missing route | Silent failures become loud exceptions | Implement `routeNotificationForFast2sms()` |
| Response objects return `ResponseInterface` | Type-hints on `Fast2smsResponse` break | Update to `ResponseInterface` |

---

## What Stays the Same

The following are **unchanged** in v2.0.0 — no migration needed:

- **Facade API**: `Fast2sms::quick()`, `Fast2sms::dlt()`, `Fast2sms::otp()`, `Fast2sms::viaWhatsApp()`, `Fast2sms::getBalance()`, etc.
- **Config file structure**: All v1 config keys remain valid; new keys are purely additive.
- **Event class names**: `SmsSent`, `SmsFailed`, `LowBalanceDetected`, `WhatsAppSent`, `WhatsAppFailed`.
- **Enum values**: `SmsRoute`, `SmsLanguage`, `WhatsAppType`, `DltManagerType` — all cases and backing values unchanged.
- **Artisan commands**: `fast2sms:balance`, `fast2sms:waba`.
- **Service provider auto-discovery**: No changes to `composer.json` `extra.laravel` keys.
- **Queue support**: `Fast2sms::queue()`, `onQueue()`, `onConnection()` — same API.

---

## New in v2.0.0: Cost-Saving Features

All six cost-saving features are **opt-in** and disabled by default. No existing code breaks. Enable them by adding the relevant keys to your published `config/fast2sms.php` or via environment variables.

### New Config Keys

| Key | Env Variable | Default | Description |
|-----|-------------|---------|-------------|
| `deduplication.enabled` | `FAST2SMS_DEDUP_ENABLED` | `false` | Throw `DuplicateSendException` on repeated identical sends |
| `deduplication.ttl` | `FAST2SMS_DEDUP_TTL` | `60` | Dedup window in seconds |
| `deduplication.store` | `FAST2SMS_DEDUP_STORE` | `null` | Cache store to use (null = default) |
| `validation.strip_invalid_recipients` | `FAST2SMS_STRIP_INVALID` | `false` | Strip invalid numbers before sending |
| `recipients.deduplicate` | `FAST2SMS_DEDUP_RECIPIENTS` | `true` | Remove duplicate numbers from recipient list |
| `recipients.batch_size` | `FAST2SMS_BATCH_SIZE` | `0` | Split large lists into chunks (0 = disabled) |
| `balance_gate.enabled` | `FAST2SMS_BALANCE_GATE` | `false` | Check balance before every send |
| `balance_gate.threshold` | `FAST2SMS_BALANCE_THRESHOLD` | `10.0` | Balance threshold in ₹ |
| `balance_gate.abort` | `FAST2SMS_BALANCE_ABORT` | `true` | Throw `InsufficientBalanceException` when below threshold |
| `throttle.enabled` | `FAST2SMS_THROTTLE_ENABLED` | `false` | Enable per-minute send-rate throttle |
| `throttle.max_per_minute` | `FAST2SMS_THROTTLE_MAX` | `60` | Maximum sends per minute |
| `throttle.store` | `FAST2SMS_THROTTLE_STORE` | `null` | Cache store to use (null = default) |

### New Exceptions

| Exception | Named Constructor | When thrown |
|-----------|------------------|-------------|
| `DuplicateSendException` | `::detected(string $hash)` | Identical send within dedup TTL window |
| `InsufficientBalanceException` | `::belowThreshold(float $balance, float $threshold)` | Balance below threshold with `abort = true` |
| `ThrottleExceededException` | `::limitReached(int $count, int $max)` | Per-minute send rate exceeded |

All three extend `Fast2smsException` — existing `catch (Fast2smsException)` blocks will catch them automatically.

### Catching the New Exceptions

```php
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;

try {
    Fast2sms::to('9876543210')->message('Hello')->send();
} catch (DuplicateSendException $e) {
    // Already sent within the dedup window — skip silently or log
} catch (InsufficientBalanceException $e) {
    // Top up balance before retrying
} catch (ThrottleExceededException $e) {
    // Back off and retry after a minute
}
```

---

## Need Help?

- Open an issue: [github.com/itxshakil/laravel-fast2sms/issues](https://github.com/itxshakil/laravel-fast2sms/issues)
- Read the full changelog: [CHANGELOG.md](./CHANGELOG.md)
