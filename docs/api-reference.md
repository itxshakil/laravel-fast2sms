# API Reference

Complete public API surface for Laravel Fast2SMS v2.0.

---

## Facade: `Fast2sms`

All methods are accessible via the `Fast2sms` facade or by injecting `BaseFast2smsService`.

```php
use Shakil\Fast2sms\Facades\Fast2sms;
```

---

## SMS Methods

### `quick()`

Send a Quick SMS.

```php
Fast2sms::quick(
    string|array $numbers,
    string $message,
    ?SmsLanguage $language = null,
): ResponseInterface
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `numbers` | `string\|array` | One or more 10-digit Indian mobile numbers |
| `message` | `string` | The message text |
| `language` | `?SmsLanguage` | Optional language override |

---

### `otp()`

Send an OTP SMS using a pre-approved template.

```php
Fast2sms::otp(
    string|array $numbers,
    string $otpValue,
): ResponseInterface
```

---

### `dlt()`

Send a DLT-registered transactional SMS.

```php
Fast2sms::dlt(
    string|array $numbers,
    string $templateId,
    array|string $variablesValues,
    ?string $senderId = null,
    ?string $entityId = null,
): ResponseInterface
```

---

### Fluent Builder

For advanced options (flash, schedule, route, etc.) use the fluent builder:

```php
Fast2sms::to(string|array $numbers)
    ->message(string $message)
    ->flash(bool $flash = true)
    ->route(SmsRoute $route)
    ->senderId(string $senderId)
    ->language(SmsLanguage $language)
    ->schedule(string|DateTimeInterface $time)
    ->send(): ResponseInterface
```

---

### `checkBalance()`

Retrieve the current wallet balance, optionally checking against a threshold.

```php
Fast2sms::checkBalance(?float $threshold = null): ResponseInterface
```

---

### `dltManager()`

Retrieve DLT registration details for the given type.

```php
Fast2sms::dltManager(DltManagerType $type): ResponseInterface
```

---

## WhatsApp Methods

### `whatsapp()`

Get the WhatsApp service instance.

```php
Fast2sms::whatsapp(): WhatsAppInterface
```

The returned `WhatsApp` instance exposes:

```php
->to(string $number): static
->sendText(string $message): WhatsAppResponse
->sendImage(string $url, ?string $caption = null): WhatsAppResponse
->sendDocument(string $url, ?string $filename = null, ?string $caption = null): WhatsAppResponse
->sendLocation(float $lat, float $lng, ?string $name = null, ?string $address = null): WhatsAppResponse
->sendInteractive(array $payload): WhatsAppResponse
->sendReaction(string $messageId, string $emoji): WhatsAppResponse
->block(string|array $numbers): WhatsAppResponse
->unblock(string|array $numbers): WhatsAppResponse
->getBlockedUsers(): WhatsAppResponse
->getDeliveryReport(string $requestId): WhatsAppResponse
->getSummary(string $from, string $to): WhatsAppResponse
->getLogs(string $from, string $to): WhatsAppResponse
->getWabaDetails(string $type = 'sender'): WhatsAppResponse
->uploadMedia(string $path, string $type): WhatsAppResponse
```

---

## Queue Methods

### `onQueue()` / `onConnection()`

Per-send queue overrides — call these before dispatching a send to override the default queue or connection for that call only.

```php
Fast2sms::onQueue(string $queue): static
Fast2sms::onConnection(string $connection): static
```

> Throws `ConfigurationException` if `queue.enabled` is `false`.

---

## Fake / Testing Methods

### `fake()`

Activate the fake — prevents real HTTP calls.

```php
$fake = Fast2sms::fake();
```

### SMS Assertions

```php
Fast2sms::assertSmsSent(?Closure $callback = null): void
Fast2sms::assertSmsNotSent(?Closure $callback = null): void
Fast2sms::assertSmsSentCount(int $count): void
Fast2sms::assertSmsSentTo(string $number): void
Fast2sms::assertSmsSentWithMessage(string $message): void
Fast2sms::assertSmsSentWithRoute(SmsRoute $route): void
```

### WhatsApp Assertions

```php
Fast2sms::assertWhatsAppSent(?Closure $callback = null): void
Fast2sms::assertWhatsAppNotSent(?Closure $callback = null): void
Fast2sms::assertWhatsAppSentCount(int $count): void
Fast2sms::assertWhatsAppSentTo(string $number): void
Fast2sms::assertWhatsAppSentWithType(WhatsAppType $type): void
```

### Combined Assertions

```php
Fast2sms::assertNothingSent(): void
Fast2sms::assertSentCount(int $count): void
Fast2sms::assertSentTimes(int $count): void
Fast2sms::assertSent(array|Closure|null $callback = null): void
Fast2sms::assertNotSent(array|Closure|null $callback = null): void
```

### Resetting the Fake

Call `reset()` on the `Fast2smsFake` instance returned by `Fast2sms::fake()`:

```php
$fake = Fast2sms::fake();
$fake->reset();
```

> `assertSent()` is a low-level generic assertion that matches across both SMS and WhatsApp recorded sends. Pass a closure for custom matching logic. `assertNotSent()` is its generic counterpart — asserts no message (or no matching message) was sent. `assertSentTimes()` asserts the exact total number of messages sent across all channels.

---

## Static Methods

### `events()`

Returns a map of all package event classes to their descriptions.

```php
Fast2sms::events(): array<class-string, string>
```

---

## Response Objects

### `SmsResponse`

| Method / Property | Type | Description |
|-------------------|------|-------------|
| `isSuccess()` | `bool` | Whether the API accepted the request |
| `requestId` | `string\|null` | Unique request ID (accessed via `__get` magic) |
| `message` | `string` | API response message (accessed via `__get` magic) |
| `data` | `array` | Raw response data (accessed via `__get` magic) |

### `WhatsAppResponse`

| Method / Property | Type | Description |
|-------------------|------|-------------|
| `isSuccess()` | `bool` | Whether the send was accepted |
| `messageId` | `string\|null` | WhatsApp message ID (accessed via `__get` magic) |
| `data` | `array` | Raw response data (accessed via `__get` magic) |

### `WalletBalanceResponse`

| Method / Property | Type | Description |
|-------------------|------|-------------|
| `balance` | `float` | Current wallet balance in ₹ |
| `isSuccess()` | `bool` | Whether the request succeeded |

### `DltManagerResponse`

| Method / Property | Type | Description |
|-------------------|------|-------------|
| `isSuccess()` | `bool` | Whether the request succeeded |
| `data` | `array` | DLT registration details |

---

## Enums

### `SmsRoute`

| Case | Value | Description |
|------|-------|-------------|
| `DLT` | `dlt` | DLT transactional route |
| `OTP` | `otp` | OTP route |
| `QUICK` | `q` | Quick/promotional route |
| `DLT_MANUAL` | `dlt_manual` | DLT with manual variables |

### `SmsLanguage`

| Case | Value |
|------|-------|
| `ENGLISH` | `english` |
| `UNICODE` | `unicode` |

### `WhatsAppType`

| Case | Value |
|------|-------|
| `TEXT` | `text` |
| `IMAGE` | `image` |
| `DOCUMENT` | `document` |
| `LOCATION` | `location` |
| `INTERACTIVE` | `interactive` |
| `REACTION` | `reaction` |
| `AUDIO` | `audio` |
| `VIDEO` | `video` |
| `STICKER` | `sticker` |

### `DltManagerType`

| Case | Value |
|------|-------|
| `SENDER` | `sender` |
| `TEMPLATE` | `template` |

---

## Exceptions

| Class | Extends | When |
|-------|---------|------|
| `Fast2smsException` | `\Exception` | Base class |
| `ConfigurationException` | `Fast2smsException` | Invalid config |
| `ValidationException` | `Fast2smsException` | Invalid input (also thrown via `allRecipientsInvalid()` when all numbers are stripped) |
| `NetworkException` | `Fast2smsException` | Connection failure |
| `ApiException` | `Fast2smsException` | Non-2xx API response |
| `AuthenticationException` | `ApiException` | HTTP 401 |
| `RateLimitException` | `ApiException` | HTTP 429 |
| `ServerException` | `ApiException` | HTTP 5xx |
| `DuplicateSendException` | `Fast2smsException` | Identical send repeated within the dedup TTL window |
| `InsufficientBalanceException` | `Fast2smsException` | Wallet balance below threshold (balance gate, abort mode) |
| `ThrottleExceededException` | `Fast2smsException` | Per-minute send-rate limit exceeded |

---

## Artisan Commands

| Command | Signature | Description |
|---------|-----------|-------------|
| Balance monitor | `fast2sms:balance {--threshold=} {--json}` | Check wallet balance |
| WABA details | `fast2sms:waba {type=number} {--json} {--refresh}` | Show WhatsApp Business Account info |
| Event list | `fast2sms:events {--json}` | List all package events |
| IDE helper | `fast2sms:ide-helper` | Generate `_ide_helper_fast2sms.php` |

---

## `SmsMessage` Credit Helpers

These methods let you estimate SMS credit consumption before sending.

```php
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;

$msg = SmsMessage::create('Hello!');

$msg->charCount();      // int   — number of characters in the message
$msg->isUnicode();      // bool  — true if the message contains non-GSM-7 characters
$msg->creditCount();    // int   — estimated number of SMS credits consumed
$msg->exceedsOneSms();  // bool  — true if the message spans more than one SMS part
```

**Credit calculation rules:**

| Encoding | Single SMS | Multi-part SMS (per part) |
|----------|-----------|---------------------------|
| GSM-7 | 160 chars | 153 chars |
| Unicode | 70 chars | 67 chars |

---

## See Also

- [SMS Guide](sms-guide.md)
- [WhatsApp Guide](whatsapp.md)
- [Testing](testing.md)
- [Error Handling](error-handling.md)
- [Cost-Saving Features](cost-saving-features.md)
