# Changelog

All notable changes to `laravel-fast2sms` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

This release is a **major version** focused on long-term maintainability, a richer exception hierarchy, improved Developer Experience, and PHP 8.3+ modern patterns.

**Highlights:**
- 🚀 **WhatsApp Support** — full WhatsApp Business API: text, image, document, location, interactive, reaction, sticker, and template messages.
- 🔔 **Laravel Notification Channels** — `SmsChannel` and `WhatsAppChannel` for use with Laravel's notification system.
- 🧱 **Typed Exception Hierarchy** — `AuthenticationException`, `RateLimitException`, `ServerException`, `NetworkException`, `ValidationException`, `ConfigurationException`.
- 🧪 **Rich Fake Assertions** — 16 new `Fast2smsFake` assertion methods for expressive test code.
- 💡 **Fluent Message Builders** — `SmsMessage` and `WhatsAppMessage` with named constructors and chainable setters.
- ⚙️ **Artisan Commands** — `fast2sms:events`, `fast2sms:ide-helper`, improved `fast2sms:balance` and `fast2sms:waba`.
- 📦 **Queue Support** — `onQueue()` / `onConnection()` fluent API with validation.
- 🧩 **PHP 8.3+ Patterns** — `readonly class` DTOs, backed enums, typed returns throughout.
- 🐘 **PHP 8.4 & 8.5 Support** — tested and compatible with PHP 8.4 and 8.5.
- 🚀 **Laravel 13 Support** — compatible with Laravel 11, 12, and 13.

The public SMS sending API (`Fast2sms::quick()`, `::dlt()`, `::otp()`, `::viaWhatsApp()`, etc.) is **unchanged**. See [UPGRADING.md](./UPGRADING.md) for a full migration guide.

### Breaking Changes ⚠️

- **`Fast2smsException` is now `final`** — extend a specific subtype (`ApiException`, `NetworkException`, etc.) instead.
- **DTOs are `readonly class`** — `Fast2smsConfig`, `SmsParameters`, `WhatsAppParameters` properties cannot be mutated after construction; reconstruct the DTO instead.
- **`ConfigValidator::validate()` throws `ConfigurationException`** instead of `\InvalidArgumentException` — update your `catch` blocks.
- **`ResponseFactory` throws `ApiException`** on unknown response types instead of `UnhandledMatchError`.
- **`Fast2smsFake` recorded calls are typed value objects** — use `sentSms()` / `sentWhatsApp()` accessors instead of accessing `$fake->recorded` directly.
- **`SmsChannel` / `WhatsAppChannel` throw `\LogicException`** when the notifiable is missing the required routing method — implement `routeNotificationForFast2sms()` / `routeNotificationForWhatsapp()`.
- **All public API methods** now return `ResponseInterface` instead of the concrete `Fast2smsResponse` — update type-hints accordingly.

### Added

- **Cost-saving features** (all opt-in via config/env):
    - **Recipient deduplication** (`fast2sms.recipients.deduplicate`) — strips duplicate numbers from the list before every SMS send.
    - **Invalid recipient stripping** (`fast2sms.validation.strip_invalid_recipients`) — validates each number against the `Fast2smsPhone` rule, logs a warning for each removed number, and throws `ValidationException::allRecipientsInvalid()` if all are invalid.
    - **Idempotency / dedup guard** (`fast2sms.deduplication.*`) — caches a hash of (recipients + message + route) for a configurable TTL; throws `DuplicateSendException` on a repeated call within the window. Also applied to WhatsApp sends.
    - **Send-rate throttle** (`fast2sms.throttle.*`) — sliding-window per-minute counter backed by the Laravel cache; throws `ThrottleExceededException` when the limit is reached. Also applied to WhatsApp sends.
    - **Balance gate** (`fast2sms.balance_gate.*`) — checks wallet balance before every send; fires `LowBalanceDetected` and optionally throws `InsufficientBalanceException` when below threshold. Also applied to WhatsApp sends.
    - **Batch splitting** (`fast2sms.recipients.batch_size`) — splits large recipient lists into chunks and issues one API call per chunk.
    - **`SmsMessage` credit helpers** — `charCount()`, `isUnicode()`, `creditCount()`, `exceedsOneSms()` for pre-send cost estimation.
- **New exception classes**: `DuplicateSendException`, `InsufficientBalanceException`, `ThrottleExceededException` — all extending `Fast2smsException` with named constructors.
- **`ValidationException::allRecipientsInvalid()`** named constructor.
- **Exception hierarchy**: `ConfigurationException`, `ValidationException`, `AuthenticationException` (HTTP 401), `RateLimitException` (HTTP 429), `ApiException`, `NetworkException`, `ServerException` — all extending `Fast2smsException`.
- **`WhatsAppSent` and `WhatsAppFailed` events** dispatched on WhatsApp send success/failure.
- **`Fast2smsFake` assertion methods**: `assertSmsSent()`, `assertSmsNotSent()`, `assertSmsSentCount()`, `assertSmsSentTo()`, `assertSmsSentWithMessage()`, `assertSmsSentWithRoute()`, `assertWhatsAppSent()`, `assertWhatsAppNotSent()`, `assertWhatsAppSentCount()`, `assertWhatsAppSentTo()`, `assertWhatsAppSentWithType()`, `assertNothingSent()`, `assertSentCount()`, `assertSentTimes()`, `assertSent()`, `assertNotSent()`, `reset()`.
- **`HandlesFaking::stopFaking()`** static method to reset the shared `$fake` instance between test cases; call it in `tearDown` when managing the fake lifecycle manually.
- **Fluent named constructors on `WhatsAppMessage`**: `text()`, `image()`, `document()`, `forLocation()`, `forInteractive()`.
- **`SmsMessage::create()`** named constructor; **`SmsMessage::withContent()`**, **`withRoute()`**, **`withNumbers()`** canonical setters.
- **`fast2sms:events` Artisan command** — lists all dispatchable events with descriptions in a table or JSON.
- **`fast2sms:ide-helper` Artisan command** — generates `_ide_helper_fast2sms.php` with full type signatures for all facade methods.
- **`onQueue()` / `onConnection()` fluent API** on `HasQueueing` — throws `ConfigurationException` if queuing is not enabled.
- **`ResponseInterface`** (`Shakil\Fast2sms\Contracts\ResponseInterface`) — new contract implemented by all response classes.
- **`Fast2smsConfig` DTO** — typed, readonly value object replacing raw `array $config`; provides full IDE autocompletion.
- **`ResponseType` enum** — explicit dispatch enum for `ResponseFactory::make()`.
- **Payload builders** (`QuickPayloadBuilder`, `DltPayloadBuilder`, `OtpPayloadBuilder`) replacing `match`-based payload construction.
- **`ConfigValidator`** — extracted config validation with URL format, driver allowlist, timeout, and WhatsApp shape checks.
- **`Fast2smsEventServiceProvider`** — dedicated event service provider with all event→listener mappings.
- **`balance_threshold`**, **`events.enabled`**, and **`queue.*`** config keys with inline documentation.
- Expanded test suite from 88 to **229 tests / 453 assertions** covering all DTOs, enums, exceptions, responses, events, jobs, channels, fake assertions, and LogClient.
- **Laravel 13 compatibility** — all `illuminate/*` constraints extended to `^11.0|^12.0|^13.0`.
- **PHP 8.4 and 8.5 support** — CI matrix extended; `^8.3` constraint covers 8.4 and 8.5.

### Changed

- **`BaseFast2smsService` constructor** now accepts `Fast2smsConfig $config` instead of `array $config`.
- **`HttpClient`** maps HTTP 401 → `AuthenticationException`, 429 → `RateLimitException`, 5xx → `ServerException`; uses manual retry loop (3 attempts, 100 ms delay) retrying on `ConnectionException` and 5xx only.
- **`LogClient`** uses `fast2sms.log_channel` config value.
- **Listeners** (`LogSmsSent`, `LogSmsFailed`, `LogWhatsAppSent`, `LogWhatsAppFailed`) use `fast2sms.log_channel`.
- **`Fast2smsServiceProvider`** is now deferred.
- **`MonitorSmsBalance` command** gains `--json` flag, styled output, and exit code 1 on low balance.
- **`WhatsAppWabaDetails` command** gains `--json` and `--refresh` flags.
- **`BaseFast2smsService::events()`** static method returns event class → description map.

### Deprecated

- `SmsMessage::content()` — use `withContent()`. Will be removed in v3.0.0.
- `SmsMessage::route()` — use `withRoute()`. Will be removed in v3.0.0.
- `SmsMessage::to()` — use `withNumbers()`. Will be removed in v3.0.0.

### Fixed

- **`BaseFast2smsService::executeApiCall`** — removed redundant `afterApiCall()` from the `finally` block; it was double-triggering state resets because callers (`Fast2sms::send`, `WhatsApp::send`) already call it in their own `finally` blocks.
- **`ManagesSms::executeSend`** — added null-check on `$response` after chunked sending loop; previously a failure on the very first chunk would cause a null-dereference instead of a descriptive `Fast2smsException`.
- **`SendRateThrottle::check`** — replaced non-atomic `Cache::get` / `put` / `increment` pattern with Laravel's `RateLimiter` to eliminate a race condition that could allow more requests than `max_per_minute`.
- **`AppliesSendGuards::applySendGuards`** — deduplication cache entry is now written only *after* a successful send; previously a failed send would block the next legitimate retry for the full TTL.
- **`HttpClient::upload`** — added file-existence and readability check before `file_get_contents()`; throws a descriptive `Fast2smsException` instead of passing `false` to `attach()` on missing or unreadable files.
- **`ManagesSms::setDlt`** — removed copy-paste `->message($templateId)` call that incorrectly set the message body to the DLT template ID string.
- **`Fast2smsServiceProvider::boot`** — fixed inverted console condition; config validation is now correctly skipped only during unit tests (`runningUnitTests()`), not during all console commands such as `artisan queue:work`.
- **`WhatsApp::send`** — added explicit `else` branch for unhandled `WhatsAppType` enum cases that throws a `ValidationException`, preventing silent fall-through when new enum cases are added in the future.
- **`ResponseFactory::fallbackDetect`** — documented the key-presence heuristic fragility with a `WARNING` docblock; callers that know the expected response type should pass a `ResponseType` hint to `ResponseFactory::make()` directly.
- `HttpClient` retry logic no longer retries on 4xx errors (only 5xx and connection failures).
- `ResponseFactory` no longer throws `UnhandledMatchError` on unknown response types.
- `HandlesFaking::fake()` now resets recorded calls between tests.
- `ManagesWhatsAppActions` dispatches `WhatsAppFailed` on exception.
- `LowBalanceDetected` event now correctly uses `Dispatchable` + `SerializesModels` traits.

## [1.3.0] - 2026-01-14

Adds **database logging**, a **log driver** for local development, automatic **HTTP retries**, and several Developer Experience improvements.

### Added

- **Observability & Logging** (see [docs/db-logging.md](./docs/db-logging.md)):
    - Optional database-backed logging system with `fast2sms_logs` table via new migration — publish with `php artisan vendor:publish --tag=fast2sms-migrations`.
    - `LogSmsSent` and `LogSmsFailed` event listeners that persist SMS records to the database.
    - `log` driver for local development to prevent credit wastage — logs requests instead of sending real SMS.
- **Resilience**:
    - Automatic retries using Laravel's `Http::retry()` (3 attempts, 100 ms backoff).
    - Config validation during boot with a clear `Fast2smsException` on misconfiguration.
- **Developer Experience**:
    - Enhanced `SmsChannel` to support `SmsMessage` objects with recipient data.
    - Fluent `to()` and `send()` methods on `SmsMessage`.
    - Support for Laravel `Collection` in the `to()` method.
    - Custom `Fast2smsPhone` validation rule for Indian mobile numbers.
    - Improved `Fast2smsResponse` with dynamic property access and `json()` method.
- **Testing**:
    - Comprehensive tests for database logging, log driver, retries, config validation, and the new validation rule.

## [1.2.0] - 2025-12-18

Compatibility release adding PHP 8.5 support.

### Added

- PHP 8.5 support

## [1.1.2] - 2025-12-18

Compatibility release adding PHP 8.5 support (patch tag).

### Added

- PHP 8.5 support

## [1.1.1] - 2025-08-22

Housekeeping release hiding unnecessary files from package installs.

### Changed

- Hidden unnecessary files from Composer package install via `.gitattributes`.

## [1.1.0] - 2025-08-19

Compatibility release adding PHP 8.4 support.

### Added

- PHP 8.4 support

## [1.0.0] - 2025-08-16

Initial public release of the `laravel-fast2sms` package.

### Added
- Initial release of Laravel Fast2SMS integration
- Fast2SMS service provider for Laravel
- Configuration file for Fast2SMS credentials and settings
- Notification channel support for Laravel
- Console command for monitoring SMS balance
- Support for sending single and bulk SMS
- Event system for SMS status tracking
- Data Transfer Objects for SMS messages
- Facade for easy access to Fast2SMS services
- Comprehensive exception handling
- Response handling and parsing
- PHPUnit test suite
- Laravel 12.x compatibility
- PHP 8.3+ support

### Security
- Secure handling of API credentials
- Input validation and sanitization
- Rate limiting support

[Unreleased]: https://github.com/itxshakil/laravel-fast2sms/compare/v2.0.1...HEAD
[2.0.1]: https://github.com/itxshakil/laravel-fast2sms/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/itxshakil/laravel-fast2sms/compare/v1.3.0...v2.0.0
[1.3.0]: https://github.com/itxshakil/laravel-fast2sms/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/itxshakil/laravel-fast2sms/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/itxshakil/laravel-fast2sms/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/itxshakil/laravel-fast2sms/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/itxshakil/laravel-fast2sms/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/itxshakil/laravel-fast2sms/releases/tag/v1.0.0
