# Events

## Available Events

### LowBalanceDetected

Fired when the SMS balance falls below the configured threshold.

```php
use Shakil\Fast2sms\Events\LowBalanceDetected;

Event::listen(function (LowBalanceDetected $event) {
    logger()->warning("Low SMS balance: {$event->balance}");
});
```


### SmsSent

Fired when an SMS is successfully sent.

```php
use Shakil\Fast2sms\Events\SmsSent;

Event::listen(function (SmsSent $event) {
    logger()->info("SMS sent to {$event->numbers}");
});
```


### SmsFailed

Fired when an SMS fails to send.

```php
use Shakil\Fast2sms\Events\SmsFailed;

Event::listen(function (SmsFailed $event) {
    logger()->error("SMS failed: {$event->error}");
});
```
