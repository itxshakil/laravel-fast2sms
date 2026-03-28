# Testing

Laravel Fast2SMS provides a powerful fake that prevents real HTTP calls during tests and offers rich assertion helpers.

---

## Activating the Fake

Call `Fast2sms::fake()` at the start of your test:

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::fake();
```

All subsequent calls to `quick()`, `otp()`, `dlt()`, and WhatsApp send methods will be intercepted — no real HTTP requests are made.

---

## SMS Assertions

```php
Fast2sms::fake();

// ... code that sends SMS

// Assert at least one SMS was sent
Fast2sms::assertSmsSent();

// Assert no SMS was sent
Fast2sms::assertSmsNotSent();

// Assert exact count
Fast2sms::assertSmsSentCount(2);

// Assert sent to a specific number
Fast2sms::assertSmsSentTo('9876543210');

// Assert sent with a specific message (substring match)
Fast2sms::assertSmsSentWithMessage('Your OTP');

// Assert sent with a specific route
use Shakil\Fast2sms\Enums\SmsRoute;
Fast2sms::assertSmsSentWithRoute(SmsRoute::QUICK);
```

### Closure-Based Assertions

For complex assertions, pass a closure that receives the `SmsParameters` DTO:

```php
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;

Fast2sms::assertSmsSent(function (SmsParameters $params): bool {
    return str_contains($params->message, 'OTP')
        && in_array('9876543210', (array) $params->numbers);
});
```

---

## WhatsApp Assertions

```php
use Shakil\Fast2sms\Enums\WhatsAppType;

// Assert at least one WhatsApp message was sent
Fast2sms::assertWhatsAppSent();

// Assert no WhatsApp message was sent
Fast2sms::assertWhatsAppNotSent();

// Assert exact count
Fast2sms::assertWhatsAppSentCount(1);

// Assert sent to a specific number
Fast2sms::assertWhatsAppSentTo('9876543210');

// Assert sent with a specific type
Fast2sms::assertWhatsAppSentWithType(WhatsAppType::TEXT);
```

### Closure-Based Assertions

For complex assertions, pass a closure that receives the `WhatsAppParameters` DTO:

```php
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;

Fast2sms::assertWhatsAppSent(function (WhatsAppParameters $params): bool {
    return $params->to === '919876543210'
        && $params->type === WhatsAppType::TEXT;
});
```

---

## Combined Assertions

```php
// Assert nothing was sent (SMS or WhatsApp)
Fast2sms::assertNothingSent();

// Assert total sends across both channels
Fast2sms::assertSentCount(3);

// Assert exact total sends via sentMessages (raw payloads via recordMessage)
// Note: assertSentCount() counts typed records (recordedSms + recordedWhatsApp);
// assertSentTimes() counts raw sentMessages — they are NOT aliases and may differ.
Fast2sms::assertSentTimes(3);

// Assert a message matching a closure was sent (generic, cross-channel; closure receives raw array payload)
Fast2sms::assertSent(fn (array $message) => $message['numbers'] === ['9876543210']);

// Assert no message matching criteria was sent (generic, cross-channel)
Fast2sms::assertNotSent();

// Assert no message matching a closure was sent (closure receives raw array payload)
Fast2sms::assertNotSent(fn (array $message) => $message['numbers'] === ['9876543210']);
```

---

## Resetting the Fake

Between tests, the fake resets automatically. To reset manually within a test:

```php
$fake = Fast2sms::fake();

// ... send messages ...

$fake->reset(); // resets recorded sends on the Fast2smsFake instance
```

### Stopping the Fake

`HandlesFaking` stores the fake instance in a **static property** shared across all instances. If you activate the fake in one test and do not stop it, subsequent tests in the same process will continue using the fake.

Call `Fast2sms::stopFaking()` in `tearDown` when you need to restore real HTTP behaviour after a test:

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Tests\TestCase;

class MyTest extends TestCase
{
    protected function tearDown(): void
    {
        Fast2sms::stopFaking();
        parent::tearDown();
    }

    public function test_something(): void
    {
        Fast2sms::fake();
        // ...
    }
}
```

> **Note:** When using Orchestra Testbench or Laravel's `RefreshDatabase`, the service container is re-booted between tests, so `stopFaking()` is usually not required. It is most useful in plain PHPUnit unit tests that share a single process without a full Laravel boot.

---

## Example Feature Test

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Tests\TestCase;

class SendOtpTest extends TestCase
{
    public function test_otp_is_sent_on_registration(): void
    {
        Fast2sms::fake();

        $this->post('/register', [
            'name'  => 'John Doe',
            'phone' => '9876543210',
            'email' => 'john@example.com',
        ])->assertRedirect('/verify');

        Fast2sms::assertSmsSentTo('9876543210');
        Fast2sms::assertSmsSentWithMessage('verification code');
        Fast2sms::assertSmsSentCount(1);
    }
}
```

---

## Example Unit Test

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function test_order_shipped_notification_sends_sms(): void
    {
        Fast2sms::fake();

        $user = User::factory()->create(['phone' => '9876543210']);
        $user->notify(new OrderShipped($order));

        Fast2sms::assertSmsSentTo('9876543210');
    }
}
```

---

## Using the Log Driver

Alternatively, set `FAST2SMS_DRIVER=log` in your `.env.testing` to log sends without making HTTP calls. Unlike `fake()`, the log driver does not support assertions.

```env
# .env.testing
FAST2SMS_DRIVER=log
```

---

## PHPStan & Static Analysis

The package ships with full PHPStan type coverage. Run static analysis with:

```bash
composer analyse
```

---

## See Also

- [Cost-Saving Features](cost-saving-features.md) — test deduplication, throttle, and balance-gate guards
- [Events & Listeners](events.md)
- [Error Handling](error-handling.md)
- [Queuing](queuing.md)
