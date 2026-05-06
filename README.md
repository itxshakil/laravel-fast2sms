<p align="center">
  <img src="https://raw.githubusercontent.com/itxshakil/laravel-fast2sms/main/art/logo.svg" width="400" alt="Laravel Fast2SMS">
</p>

<p align="center">
  <a href="https://packagist.org/packages/itxshakil/laravel-fast2sms">
    <img src="https://img.shields.io/packagist/v/itxshakil/laravel-fast2sms" alt="Latest Version">
  </a>
  <a href="https://packagist.org/packages/itxshakil/laravel-fast2sms">
    <img src="https://img.shields.io/packagist/dt/itxshakil/laravel-fast2sms" alt="Total Downloads">
  </a>
  <a href="https://github.com/itxshakil/laravel-fast2sms/actions">
    <img src="https://github.com/itxshakil/laravel-fast2sms/actions/workflows/ci.yml/badge.svg" alt="Tests">
  </a>
  <a href="https://opensource.org/licenses/MIT">
    <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
  </a>
</p>

---

**Laravel Fast2SMS** is a first-class Laravel package for sending SMS and WhatsApp messages via the [Fast2SMS](https://www.fast2sms.com) API. It provides a fluent, type-safe API, Laravel Notifications support, queue integration, a powerful fake for testing, and a rich exception hierarchy — so you can build reliable messaging features with confidence.

The most complete Fast2SMS integration for Laravel:
- SMS + OTP + DLT + WhatsApp
- Queue + Notifications
- Prevents wasting SMS money in production

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Cost-Saving Features](#cost-saving-features)
- [Sending SMS](#sending-sms)
- [Sending WhatsApp Messages](#sending-whatsapp-messages)
- [Laravel Notifications](#laravel-notifications)
- [Queuing](#queuing)
- [Events & Listeners](#events--listeners)
- [Testing](#testing)
- [Artisan Commands](#artisan-commands)
- [Phone Validation](#phone-validation)
- [Error Handling](#error-handling)
- [Upgrade Guide](#upgrade-guide)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- 📱 **SMS** — Quick, OTP, and DLT routes with flash and bulk support
- 💬 **WhatsApp** — Text, image, document, location, and interactive messages
- 🔔 **Laravel Notifications** — `SmsChannel` and `WhatsAppChannel` out of the box
- ⚡ **Queue support** — Dispatch sends as background jobs with per-send overrides
- 🧪 **Fake & assert** — `Fast2sms::fake()` with 16 rich assertion helpers
- 🚨 **Typed exceptions** — `AuthenticationException`, `RateLimitException`, `ApiException`, and more
- 💰 **Cost-saving features** — Recipient deduplication (on by default), dedup guard, throttle, balance gate, batch splitting, invalid-recipient stripping
- 🔍 **PHPStan level 6** — Fully typed, zero suppressions
- 🛠 **Artisan commands** — Balance monitor, WABA details, event discovery, IDE helper
- 🇮🇳 **Indian phone validation** — `Fast2smsPhone` rule class

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | ^8.3 |
| Laravel | ^11.0 \| ^12.0 \| ^13.0 |
| Fast2SMS account | [Sign up free](https://www.fast2sms.com) |

---

## Installation

Install via Composer:

```bash
composer require itxshakil/laravel-fast2sms
```

The package auto-discovers its service provider. Publish the config file:

```bash
php artisan vendor:publish --tag=fast2sms-config
```

Add your API key to `.env`:

```env
FAST2SMS_API_KEY=your_api_key_here
```

> **Full installation guide:** [docs/installation.md](docs/installation.md)

---

## Quick Start

```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Send a quick SMS
$response = Fast2sms::quick(
    numbers: '9876543210',
    message: 'Your OTP is 123456',
);

if ($response->isSuccess()) {
    echo 'SMS sent! Request ID: ' . $response->requestId;
}
```

---

## Configuration

After publishing, edit `config/fast2sms.php`. All keys can be set via environment variables:

| Key | Env Variable | Default | Description |
|-----|-------------|---------|-------------|
| `api_key` | `FAST2SMS_API_KEY` | — | Your Fast2SMS API key (required) |
| `default_sender_id` | `FAST2SMS_DEFAULT_SENDER_ID` | `FSTSMS` | Default DLT sender ID |
| `default_route` | `FAST2SMS_DEFAULT_ROUTE` | `dlt` | Default SMS route |
| `base_url` | — | `https://www.fast2sms.com/dev` | Fast2SMS API base URL (do not change unless instructed) |
| `timeout` | — | `30` | HTTP request timeout in seconds |
| `driver` | `FAST2SMS_DRIVER` | `api` | `api` for real sends, `log` for local dev |
| `database_logging` | `FAST2SMS_DATABASE_LOGGING` | `false` | Log sends to database — see [docs/db-logging.md](./docs/db-logging.md) |
| `recipients.deduplicate` | `FAST2SMS_DEDUP_RECIPIENTS` | `true` | Remove duplicate numbers before every send |
| `recipients.batch_size` | `FAST2SMS_BATCH_SIZE` | `0` | Split large lists into chunks (`0` = disabled) |
| `validation.strip_invalid_recipients` | `FAST2SMS_STRIP_INVALID` | `false` | Strip invalid numbers before sending |
| `balance_gate.enabled` | `FAST2SMS_BALANCE_GATE` | `false` | Enable balance check before every send |
| `balance_gate.threshold` | `FAST2SMS_BALANCE_THRESHOLD` | `10.0` | Balance alert threshold (₹) |
| `balance_gate.abort` | `FAST2SMS_BALANCE_ABORT` | `true` | Throw `InsufficientBalanceException` when balance is low |
| `deduplication.enabled` | `FAST2SMS_DEDUP_ENABLED` | `false` | Enable idempotency / dedup guard |
| `deduplication.ttl` | `FAST2SMS_DEDUP_TTL` | `60` | Dedup window in seconds |
| `deduplication.store` | `FAST2SMS_DEDUP_STORE` | `null` | Cache store for dedup guard (`null` = default store) |
| `throttle.enabled` | `FAST2SMS_THROTTLE_ENABLED` | `false` | Enable send-rate throttle |
| `throttle.max_per_minute` | `FAST2SMS_THROTTLE_MAX` | `60` | Max sends per minute |
| `throttle.store` | `FAST2SMS_THROTTLE_STORE` | `null` | Cache store for send-rate throttle (`null` = default store) |
| `whatsapp.default_phone_number_id` | `FAST2SMS_WHATSAPP_PHONE_NUMBER_ID` | — | Default WhatsApp phone number ID |
| `whatsapp.default_waba_id` | `FAST2SMS_WHATSAPP_WABA_ID` | — | Default WhatsApp Business Account ID |
| `whatsapp.version` | `FAST2SMS_WHATSAPP_VERSION` | `v24.0` | WhatsApp API version |
| `events.enabled` | `FAST2SMS_EVENTS_ENABLED` | `true` | Enable/disable event dispatching |
| `queue.enabled` | `FAST2SMS_QUEUE_ENABLED` | `false` | Send via queue |
| `queue.connection` | `FAST2SMS_QUEUE_CONNECTION` | `null` | Queue connection name |
| `queue.name` | `FAST2SMS_QUEUE_NAME` | `default` | Queue name |
| `queue.tries` | `FAST2SMS_QUEUE_TRIES` | `3` | Max job attempts |

Use `FAST2SMS_DRIVER=log` in local/testing environments to log messages instead of making real API calls.

> **Full configuration reference:** [docs/configuration.md](docs/configuration.md)

---

## Cost-Saving Features

Most cost-saving features are **opt-in** and disabled by default — enable only what you need. Recipient deduplication is the exception: it is **enabled by default**.

### Recipient Deduplication

Automatically removes duplicate numbers from the recipient list before every send. This is **enabled by default** — set to `false` to disable:

```env
FAST2SMS_DEDUP_RECIPIENTS=true   # default: true
```

### Invalid Recipient Stripping

Validates each number against the `Fast2smsPhone` rule, logs a warning for each removed number, and throws `ValidationException` if all numbers are invalid:

```env
FAST2SMS_STRIP_INVALID=true
```

### Idempotency / Dedup Guard

Prevents the same message being sent twice within a configurable window (applies to both SMS and WhatsApp):

```env
FAST2SMS_DEDUP_ENABLED=true
FAST2SMS_DEDUP_TTL=60   # seconds
```

Throws `DuplicateSendException` on a repeated identical call within the TTL.

### Send-Rate Throttle

Limits the number of sends per minute using a sliding-window cache counter (applies to both SMS and WhatsApp):

```env
FAST2SMS_THROTTLE_ENABLED=true
FAST2SMS_THROTTLE_MAX=60
```

Throws `ThrottleExceededException` when the limit is reached.

### Balance Gate

Checks your wallet balance before every send and fires `LowBalanceDetected`. When `abort` is `true`, throws `InsufficientBalanceException` to prevent the API call (applies to both SMS and WhatsApp):

```env
FAST2SMS_BALANCE_GATE=true
FAST2SMS_BALANCE_THRESHOLD=10.0
FAST2SMS_BALANCE_ABORT=true
```

### Batch Splitting

Splits large recipient lists into chunks and issues one API call per chunk:

```env
FAST2SMS_BATCH_SIZE=100   # 0 = disabled
```

### SMS Credit Estimation

Use `SmsMessage` helpers to estimate cost before sending:

```php
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;

$msg = new SmsMessage('Your OTP is 123456');

$msg->charCount();      // 22
$msg->isUnicode();      // false
$msg->creditCount();    // 1
$msg->exceedsOneSms();  // false
```

> **Full cost-saving features guide:** [docs/cost-saving-features.md](docs/cost-saving-features.md)

> **Full upgrade guide:** [UPGRADING.md](./UPGRADING.md#new-in-v200-cost-saving-features)

---

## Sending SMS

### Quick SMS

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::quick(numbers: '9876543210', message: 'Hello from Fast2SMS!');
```

### OTP SMS

```php
Fast2sms::otp(
    numbers: '9876543210',
    otpValue: '123456',
);
```

### DLT SMS

```php
Fast2sms::dlt(
    numbers: ['9876543210', '9123456789'],
    templateId: 'your_template_id',
    variablesValues: 'Your order #1234 has been shipped.',
    senderId: 'MYSHOP',
);
```

### Flash SMS

```php
Fast2sms::to('9876543210')
    ->message('Flash message!')
    ->flash()
    ->send();
```

### Multiple Recipients

```php
Fast2sms::quick(
    numbers: ['9876543210', '9123456789', '9000000001'],
    message: 'Broadcast message',
);
```

> **Full SMS guide:** [docs/sms-guide.md](docs/sms-guide.md)

---

## Sending WhatsApp Messages

> **Phone number format for WhatsApp:** Always include the country code prefix (e.g. `919876543210` for India — `91` + 10-digit number). This differs from SMS, which accepts 10-digit numbers directly.

### Text

```php
Fast2sms::whatsapp()
    ->to('919876543210')
    ->sendText('Hello from Fast2SMS!');
```

### Image

```php
Fast2sms::whatsapp()
    ->to('919876543210')
    ->sendImage('https://example.com/image.jpg');
```

### Document

```php
Fast2sms::whatsapp()
    ->to('919876543210')
    ->sendDocument('https://example.com/invoice.pdf');
```

### Location

```php
Fast2sms::whatsapp()
    ->to('919876543210')
    ->sendLocation(latitude: 28.6139, longitude: 77.2090, name: 'New Delhi');
```

### Interactive

```php
Fast2sms::whatsapp()
    ->to('919876543210')
    ->sendInteractive([
        'type' => 'button',
        'body' => ['text' => 'Choose an option'],
        'action' => ['buttons' => [/* ... */]],
    ]);
```

> **Convenience alias:** `Fast2sms::viaWhatsApp($to)` is shorthand for `Fast2sms::whatsapp()->to($to)`. Both are public API; `whatsapp()->to(...)` is the canonical form.

> **Full WhatsApp guide:** [docs/whatsapp.md](docs/whatsapp.md)

---

## Laravel Notifications

### SMS Notification

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;

class OrderShipped extends Notification
{
    public function via(object $notifiable): array
    {
        return ['fast2sms'];
    }

    public function toSms(object $notifiable): SmsMessage
    {
        return SmsMessage::create("Your order #{$this->order->id} has shipped!")
            ->withRoute(SmsRoute::QUICK);
    }
}
```

Add `routeNotificationForFast2sms()` to your **notifiable model** (e.g. `User`) to tell the channel which number to use:

```php
// app/Models/User.php
public function routeNotificationForFast2sms(): string
{
    return $this->phone_number;
}
```

### WhatsApp Notification

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Notifications\Messages\WhatsAppMessage;

class OrderShippedWhatsApp extends Notification
{
    public function via(object $notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::text("Your order #{$this->order->id} has shipped!");
    }
}
```

Add `routeNotificationForWhatsapp()` to your **notifiable model** (e.g. `User`) to tell the channel which number to use:

```php
// app/Models/User.php
public function routeNotificationForWhatsapp(): string
{
    return $this->phone_number;
}
```

> **Full notifications guide:** [docs/notifications.md](docs/notifications.md)

---

## Queuing

Enable queuing in `.env`:

```env
FAST2SMS_QUEUE_ENABLED=true
FAST2SMS_QUEUE_NAME=sms
FAST2SMS_QUEUE_TRIES=3
```

Then send as usual — the package dispatches a background job automatically:

```php
Fast2sms::quick(numbers: '9876543210', message: 'Queued message');
```

Override queue settings per-send:

```php
Fast2sms::onQueue('high-priority')
    ->onConnection('redis')
    ->quick(numbers: '9876543210', message: 'Urgent!');
```

> **Full queuing guide:** [docs/queuing.md](docs/queuing.md)

---

## Events & Listeners

The package dispatches the following events:

| Event | When |
|-------|------|
| `SmsSent` | After a successful SMS send |
| `SmsFailed` | When an SMS send fails |
| `WhatsAppSent` | After a successful WhatsApp send |
| `WhatsAppFailed` | When a WhatsApp send fails |
| `LowBalanceDetected` | When wallet balance drops below threshold |

### Listening to Events

```php
// In EventServiceProvider
protected $listen = [
    \Shakil\Fast2sms\Events\SmsSent::class => [
        \App\Listeners\LogSmsSent::class,
    ],
    \Shakil\Fast2sms\Events\LowBalanceDetected::class => [
        \App\Listeners\NotifyAdminOfLowBalance::class,
    ],
];
```

### Discover All Events

```bash
php artisan fast2sms:events
```

> **Full events guide:** [docs/events.md](docs/events.md)

---

## Testing

Use `Fast2sms::fake()` to prevent real HTTP calls in tests:

```php
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::fake();

// Run code that sends SMS...
$this->post('/send-otp', ['phone' => '9876543210']);

// Assert
Fast2sms::assertSmsSent();
Fast2sms::assertSmsSentTo('9876543210');
Fast2sms::assertSmsSentWithMessage('Your OTP');
Fast2sms::assertSmsSentCount(1);
Fast2sms::assertSmsNotSent();
Fast2sms::assertSmsSentWithRoute(SmsRoute::QUICK);

// Closure-based assertion
Fast2sms::assertSmsSent(function (SmsParameters $params): bool {
    return str_contains($params->message, 'OTP');
});

// Assert nothing was sent
Fast2sms::assertNothingSent();
```

### WhatsApp Assertions

```php
use Shakil\Fast2sms\Enums\WhatsAppType;

Fast2sms::assertWhatsAppSent();
Fast2sms::assertWhatsAppSentTo('919876543210');
Fast2sms::assertWhatsAppSentCount(1);
Fast2sms::assertWhatsAppSentWithType(WhatsAppType::TEXT);
Fast2sms::assertWhatsAppNotSent();

// Closure-based assertion
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;

Fast2sms::assertWhatsAppSent(function (WhatsAppParameters $params): bool {
    return $params->to === '919876543210'
        && $params->type === WhatsAppType::TEXT;
});
```

### Combined Assertions

```php
// Assert nothing was sent (SMS or WhatsApp)
Fast2sms::assertNothingSent();

// Assert total sends across both channels (counts typed SMS + WhatsApp records)
Fast2sms::assertSentCount(3);

// Assert exact total sends via raw sentMessages log entries
Fast2sms::assertSentTimes(3);

// Generic low-level assertion with optional closure (closure receives raw array payload)
Fast2sms::assertSent(fn (array $message) => $message['numbers'] === ['9876543210']);

// Assert no message matching criteria was sent
Fast2sms::assertNotSent(fn (array $message) => $message['numbers'] === ['9876543210']);
```

### Stopping the Fake

`Fast2sms::fake()` stores the fake in a static property. Call `Fast2sms::stopFaking()` in `tearDown` to restore real HTTP behaviour when managing the fake lifecycle manually in plain PHPUnit tests:

```php
protected function tearDown(): void
{
    Fast2sms::stopFaking();
    parent::tearDown();
}
```

> **Note:** When using Orchestra Testbench or Laravel's full boot cycle the container is re-booted between tests, so `stopFaking()` is usually not required.

> **Full testing guide:** [docs/testing.md](docs/testing.md)

---

## Artisan Commands

| Command | Description |
|---------|-------------|
| `fast2sms:balance` | Check wallet balance and alert if below threshold |
| `fast2sms:waba` | Show WhatsApp Business Account details |
| `fast2sms:events` | List all package events |
| `fast2sms:ide-helper` | Generate IDE helper stub for better autocomplete *(non-production only)* |

```bash
# Check balance with custom threshold
php artisan fast2sms:balance --threshold=500

# Output as JSON (for scripting/CI)
php artisan fast2sms:balance --json

# Show WABA details
php artisan fast2sms:waba

# List all events
php artisan fast2sms:events

# Generate IDE helper
php artisan fast2sms:ide-helper
```

---

## Phone Validation

Use the `Fast2smsPhone` rule to validate Indian mobile numbers:

```php
use Shakil\Fast2sms\Rules\Fast2smsPhone;

$request->validate([
    'phone' => ['required', 'string', new Fast2smsPhone],
]);
```

The rule validates 10-digit Indian mobile numbers starting with 6–9.

---

## Error Handling

All exceptions extend `Fast2smsException`, so you can catch broadly or specifically:

```php
use Shakil\Fast2sms\Exceptions\ApiException;
use Shakil\Fast2sms\Exceptions\AuthenticationException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\NetworkException;
use Shakil\Fast2sms\Exceptions\RateLimitException;
use Shakil\Fast2sms\Exceptions\ValidationException;

try {
    Fast2sms::quick(numbers: '9876543210', message: 'Hello!');
} catch (AuthenticationException $e) {
    // Invalid API key — check FAST2SMS_API_KEY
} catch (RateLimitException $e) {
    // Too many requests — back off and retry
} catch (ValidationException $e) {
    // Invalid input — $e->getMessage() describes the problem
} catch (ApiException $e) {
    // API returned an error — check $e->getMessage()
} catch (NetworkException $e) {
    // Network timeout or connection failure
} catch (Fast2smsException $e) {
    // Catch-all for any other package exception
}
```

### Exception Reference

| Exception | When |
|-----------|------|
| `ConfigurationException` | Invalid config (missing API key, bad driver) |
| `ValidationException` | Invalid input (empty numbers, bad phone format) |
| `AuthenticationException` | Invalid or missing API key (HTTP 401) |
| `RateLimitException` | Too many requests (HTTP 429) |
| `ServerException` | Fast2SMS server error (HTTP 5xx) |
| `ApiException` | Other API error (4xx) |
| `NetworkException` | Network timeout or connection failure |
| `DuplicateSendException` | Identical send repeated within the deduplication TTL |
| `InsufficientBalanceException` | Wallet balance too low when Balance Gate `abort` is `true` |
| `ThrottleExceededException` | Send-rate limit reached for the configured window |

> **Full error handling guide:** [docs/error-handling.md](docs/error-handling.md)

> **Full API reference:** [docs/api-reference.md](docs/api-reference.md)

---

## Upgrade Guide

Upgrading from v1.x? See **[UPGRADING.md](UPGRADING.md)** for the full migration guide, including:

- New exception types to catch
- Readonly DTO changes
- Fake assertion API changes
- Deprecated method renames (`content()` → `withContent()`, `to()` → `withNumbers()`)
- Response type-hint updates

Also available as an extended guide: [docs/upgrade-v1-to-v2.md](docs/upgrade-v1-to-v2.md)

---

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting a pull request.

```bash
# Run the full test suite
composer test

# Static analysis
composer analyse

# Auto-fix code style
composer lint:fix

# Full QA pipeline
composer qa
```

---

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

---

<p align="center">
  Made with ❤️ for the Laravel community &middot; <a href="https://www.shakiltech.com">Shakil Alam</a>
</p>
