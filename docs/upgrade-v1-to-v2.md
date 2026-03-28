# Upgrading from v1.x to v2.0

This guide provides a detailed walkthrough for migrating from Laravel Fast2SMS v1.x to v2.0. For a quick summary, see [UPGRADING.md](../UPGRADING.md).

---

## Breaking Changes Summary

| Area | v1.x | v2.0 |
|------|------|------|
| Exception base | `Fast2smsException` only | Full hierarchy (Auth, RateLimit, etc.) |
| DTOs | Mutable classes | `readonly class` — immutable |
| Fake assertions | `assertSent()` (generic only) | `assertSmsSent()`, `assertSmsNotSent()`, etc. (typed); `assertSent()` still exists as a low-level generic method |
| `SmsMessage::content()` | Active method | Deprecated → use `withContent()` |
| `SmsMessage::route()` | Active method | Deprecated → use `withRoute()` |
| `SmsMessage::to()` | Active method | Deprecated → use `withNumbers()` |

---

## Step-by-Step Migration

### Step 1 — Update the Package

```bash
composer require itxshakil/laravel-fast2sms:^2.0
```

---

### Step 2 — Update Exception Catches

v2.0 introduces a typed exception hierarchy. Update your catch blocks to use specific exception types:

**Before (v1.x):**

```php
use Shakil\Fast2sms\Exceptions\Fast2smsException;

try {
    Fast2sms::quick(...);
} catch (Fast2smsException $e) {
    // handle all errors
}
```

**After (v2.0):**

```php
use Shakil\Fast2sms\Exceptions\AuthenticationException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\RateLimitException;

try {
    Fast2sms::quick(...);
} catch (AuthenticationException $e) {
    // 401 — bad API key
} catch (RateLimitException $e) {
    // 429 — slow down
} catch (Fast2smsException $e) {
    // everything else
}
```

> Catching `Fast2smsException` still works as a catch-all — no change required if you prefer broad handling.

---

### Step 3 — Update DTO Usage

DTOs are now `readonly class`. You can no longer mutate properties after construction:

**Before (v1.x):**

```php
$params = new SmsParameters(...);
$params->message = 'Updated message'; // worked in v1
```

**After (v2.0):**

```php
// Create a new instance instead
$params = new SmsParameters(message: 'Updated message', ...);
```

---

### Step 4 — Update Fake Assertions

The fake API has been completely rewritten with typed, descriptive assertion methods:

**Before (v1.x):**

```php
Fast2sms::assertSent();    // only generic method available
Fast2sms::assertNotSent(); // generic — no typed channel variants
```

**After (v2.0):**

```php
// SMS-specific (new in v2)
Fast2sms::assertSmsSent();
Fast2sms::assertSmsNotSent();
Fast2sms::assertSmsSentTo('9876543210');
Fast2sms::assertSmsSentWithMessage('OTP');
Fast2sms::assertSmsSentCount(1);

// Combined
Fast2sms::assertNothingSent();
Fast2sms::assertSentCount(2);

// Low-level generic (still available in v2) — closure receives raw array payload
Fast2sms::assertSent(fn (array $message) => $message['numbers'] === ['9876543210']);
```

> `assertSent()` survived into v2 as a low-level escape hatch that accepts a closure for custom assertions across both SMS and WhatsApp sends. `assertNotSent()` also survived into v2 as a generic counterpart — use `assertSmsNotSent()` or `assertWhatsAppNotSent()` for typed, channel-specific assertions.

---

### Step 5 — Update SmsMessage Methods

The old fluent methods are deprecated and will be removed in v3.0.0:

| Deprecated | Replacement |
|-----------|-------------|
| `->content('...')` | `->withContent('...')` |
| `->route(SmsRoute::QUICK)` | `->withRoute(SmsRoute::QUICK)` |
| `->to('9876543210')` | `->withNumbers('9876543210')` |

**Before (v1.x):**

```php
SmsMessage::create('Hello')
    ->content('Hello')
    ->route(SmsRoute::QUICK)
    ->to('9876543210');
```

**After (v2.0):**

```php
SmsMessage::create('Hello')
    ->withRoute(SmsRoute::QUICK)
    ->withNumbers('9876543210');
```

---

## What Stays the Same

- The `Fast2sms` facade and core method signatures (`quick()`, `otp()`, `dlt()`, `whatsapp()`, etc.)
- Config file structure and all environment variable names
- `SmsChannel` notification channel
- `Fast2smsPhone` validation rule
- All Artisan commands
- Queue job behaviour

---

## New in v2.0 — Cost-Saving Features

v2.0 ships six opt-in features to reduce unnecessary API calls and protect your SMS budget. They are all **disabled by default** and require no changes to existing code.

| Feature | Config key | Env variable | Exception |
|---------|-----------|--------------|-----------|
| Recipient deduplication | `fast2sms.recipients.deduplicate` | `FAST2SMS_DEDUP_RECIPIENTS` | — |
| Invalid recipient stripping | `fast2sms.validation.strip_invalid_recipients` | `FAST2SMS_STRIP_INVALID` | `ValidationException` |
| Idempotency / dedup guard | `fast2sms.deduplication.enabled` | `FAST2SMS_DEDUP_ENABLED` | `DuplicateSendException` |
| Send-rate throttle | `fast2sms.throttle.enabled` | `FAST2SMS_THROTTLE_ENABLED` | `ThrottleExceededException` |
| Balance gate | `fast2sms.balance_gate.enabled` | `FAST2SMS_BALANCE_GATE` | `InsufficientBalanceException` |
| Batch splitting | `fast2sms.recipients.batch_size` | `FAST2SMS_BATCH_SIZE` | — |

New exception classes to catch (all extend `Fast2smsException`):

- `Shakil\Fast2sms\Exceptions\DuplicateSendException`
- `Shakil\Fast2sms\Exceptions\InsufficientBalanceException`
- `Shakil\Fast2sms\Exceptions\ThrottleExceededException`

> See [Cost-Saving Features](cost-saving-features.md) for full documentation, config keys, env variables, and usage examples.

---

## See Also

- [UPGRADING.md](../UPGRADING.md) — quick reference
- [CHANGELOG.md](../CHANGELOG.md) — full change log
- [Cost-Saving Features](cost-saving-features.md)
- [API Reference](api-reference.md)
- [Error Handling](error-handling.md)
- [Testing](testing.md)
