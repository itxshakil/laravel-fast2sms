<!-- Auto-generated guidance for AI coding agents in this repository -->
# Copilot / AI Agent Instructions — laravel-fast2sms

Purpose: Provide concise, actionable knowledge for an AI to be immediately productive in this Laravel package.

- **Big picture**
  - The package is a Laravel integration for the Fast2sms API. The main service is `src/Fast2sms.php` (high-level API) built on `src/BaseFast2smsService.php` (HTTP client, response mapping).
  - `src/Fast2smsServiceProvider.php` registers the `fast2sms` singleton and publishes `config/fast2sms.php` (tag: `fast2sms-config`).
  - Sending flows: callers configure parameters on the `Fast2sms` service (chainable methods in `Traits/ManagesSmsParameters.php`), then call `->send()` which builds a payload and dispatches via `BaseFast2smsService::executeApiCall()`.
  - Routes: three primary routes — DLT (template-driven), QUICK, OTP. Default route comes from `config/fast2sms.php` (`default_route`).

- **Key files to inspect**
  - `src/Fast2sms.php` — public API and helpers (`quick`, `dlt`, `otp`, fluent `to()->route()->send()` patterns).
  - `src/BaseFast2smsService.php` — HTTP setup (`http()`), retries, multipart payload, and response mapping to `Responses/*` classes.
  - `src/Channels/SmsChannel.php` — Notification channel integration and expected `toSms()` contract on notifications.
  - `src/Jobs/SendSmsJob.php` — how queued SMS are handled; `config/fast2sms.php` contains queue defaults.
  - `src/Traits/HandlesFaking.php` — testing pattern for faking HTTP calls and assertions: `Fast2sms::fake()`, `assertSent()`, `sentMessages()`.
  - `config/fast2sms.php` — default API endpoint, timeout, sender id and threshold values.
  - `tests/TestCase.php` — uses `orchestra/testbench`; package providers and test environment setup live here.

- **Developer workflows (commands)**
  - Install dependencies: `composer install`.
  - Run tests: `composer test` (runs `phpunit` per `composer.json`). For targeted tests: `vendor/bin/phpunit tests/Feature/SmsSendingTest.php`.
  - Lint/format: `composer lint` (runs `laravel/pint`).
  - Publish config when testing integration in a real app: `php artisan vendor:publish --tag=fast2sms-config`.
  - Artisan command: `php artisan sms:monitor` — registered by `Fast2smsServiceProvider` (see `Console/Commands`).

- **Testing & fakes**
  - Tests use `orchestra/testbench`. `tests/TestCase.php` sets `fast2sms` config values (including `api_key` = `test_api_key`) via `defineEnvironment()`.
  - To prevent network calls in tests, call `Fast2sms::fake()` (see `src/Traits/HandlesFaking.php`) which fakes `Http::` and records multipart payloads in `sentMessages()`.
  - Assertions: `Fast2sms::assertSent()`, `assertNotSent()`, and `assertSentTimes()`; tests depend on these exact method names and semantics.

- **API & HTTP details**
  - HTTP client uses Laravel's `Http` facade configured in `BaseFast2smsService::http()` with `Authorization` header set to `config('fast2sms.api_key')` and `asMultipart()`.
  - Default API base is `https://www.fast2sms.com/dev` and default endpoint for sends is `/bulkV2`. Balance uses `/wallet`.
  - Responses map to `Responses/*` classes — read `BaseFast2smsService::mapApiResponse()` to understand which response class will be used.

- **Common patterns and repo conventions**
  - Fluent setter pattern: most public methods on `Fast2sms` return `$this`, enabling chains like `Fast2sms::to(...)->route(...)->templateId(...)->send()`.
  - DTO usage: `DataTransferObjects/SmsParameters.php` is used to serialize job payloads (see `SendSmsJob`).
  - Events: package dispatches `Events\n+    - `SmsSent` on success, `SmsFailed` on failure, and `LowBalanceDetected` for balance checks. Use these hooks when adding integrations.
  - Exceptions: `Shakil\\Fast2sms\\Exceptions\\Fast2smsException` is the package-level exception class — thrown liberally for config/validation/API errors.

- **When editing code, watch for**
  - Parameter reset: `afterApiCall()` is invoked in `BaseFast2smsService::executeApiCall()`; state resets happen there — keep side effects minimal.
  - Multipart payload shape: code converts payload to multipart arrays before sending. Tests and fakes expect that structure.
  - Tests rely on `fast2sms.api_key` existing in config; if adding new tests, ensure `tests/TestCase.php` sets config accordingly.

- **Examples (copy-ready)**
  - Send DLT SMS (used in README and tests):
    ```php
    Fast2sms::dlt(numbers: '9999999999', templateId: 'TID', variablesValues: ['John'], senderId: 'SENDER');
    ```
  - Fake in tests:
    ```php
    Fast2sms::fake();
    // run action that sends sms
    Fast2sms::assertSent(['template_id' => 'TID']);
    ```

If anything in this doc is unclear or you want more detail (examples, additional file links, or merged content from other agent docs), tell me which sections to expand and I will iterate.
