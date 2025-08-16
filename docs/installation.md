# Installation

## Requirements

- PHP 8.3 or higher
- Laravel 12.x

## Installation Steps

1. Install the package via Composer:
```bash
composer require itxshakil/laravel-fast2sms
```
2. Publish the configuration file:
```bash
php artisan vendor:publish --tag=fast2sms-config
```
3. Add your Fast2SMS credentials to your `.env` file:
```env
FAST2SMS_API_KEY="your-api-key-here"
FAST2SMS_DEFAULT_SENDER_ID="FSTSMS"
FAST2SMS_DEFAULT_ROUTE="dlt"
```

# Configuration

## Environment Variables

The package uses the following environment variables:
```env
FAST2SMS_API_KEY=your-api-key
FAST2SMS_DEFAULT_SENDER_ID=FSTSMS
FAST2SMS_DEFAULT_ROUTE=dlt
```
## Configuration File

After publishing, you can find the configuration file at `config/fast2sms.php`:
```php
return [
    'api_key' => env('FAST2SMS_API_KEY'),
    'default_route' => env('FAST2SMS_DEFAULT_ROUTE', 'dlt'),
    'default_sender_id' => env('FAST2SMS_DEFAULT_SENDER_ID', 'FSTSMS'),
    'balance_threshold' => env('FAST2SMS_BALANCE_THRESHOLD', 1000),
];
```
### Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `api_key` | Your Fast2SMS API key | - |
| `default_route` | Default SMS route (dlt/quick/otp) | 'dlt' |
| `default_sender_id` | Default sender ID | 'FSTSMS' |
| `balance_threshold` | Balance threshold for notifications | 1000 |


# Basic Usage

## Quick Start
```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Send a Quick SMS
Fast2sms::quick('9999999999', 'Hello, World!');

// Send a DLT SMS
Fast2sms::dlt(
numbers: '9999999999',
templateId: 'YOUR_TEMPLATE_ID',
variablesValues: ['John Doe'],
senderId: 'YOUR_SENDER_ID'
);

// Send an OTP
Fast2sms::otp('9999999999', '123456');
```
## Using the Fluent Interface
```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\SmsRoute;

Fast2sms::to('9999999999')
->route(SmsRoute::DLT)
->senderId('YOUR_SENDER_ID')
->templateId('YOUR_TEMPLATE_ID')
->variables(['John Doe'])
->send();
```
## Checking Balance
```php
$response = Fast2sms::checkBalance();

if ($response->success()) {
echo "Balance: {$response->balance}";
echo "SMS Count: {$response->smsCount}";
}
```

# Advanced Usage

## Multiple Recipients
```php
Fast2sms::quick(['9999999999', '8888888888'], 'Bulk message');
```
## Unicode Messages
```php
use Shakil\Fast2sms\Enums\SmsLanguage;

Fast2sms::quick(
'9999999999',
'नमस्ते! यह एक क्विक एसएमएस है।',
SmsLanguage::UNICODE
);
```
## Flash Messages
```php
Fast2sms::to('9999999999')
->message('Flash message')
->flash(true)
->send();
```
## Scheduled Messages
```php
Fast2sms::to('9999999999')
->message('Scheduled message')
->schedule(now()->addHours(2))
->send();
```
## DLT Template Management

```php
use Shakil\Fast2sms\Enums\DltManagerType;

$response = Fast2sms::dltManager(DltManagerType::TEMPLATE);
foreach ($response->getTemplates() as $template) {
    echo "Template ID: {$template['template_id']}\n";
    echo "Content: {$template['message']}\n";
}
```

<llm-snippet-file>docs/notifications.md</llm-snippet-file>
# Laravel Notifications

## Setup

Add the notification channel to your notifiable model:

```php
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForFast2sms()
    {
        return $this->phone;
    }
}
```


## Creating SMS Notifications

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Facades\Fast2sms;

class SmsNotification extends Notification
{
    public function via($notifiable)
    {
        return ['fast2sms'];
    }

    public function toFast2sms($notifiable)
    {
        return Fast2sms::to($notifiable->phone)
            ->message('Your notification message')
            ->send();
    }
}
```


## Sending Notifications

```php
$user->notify(new SmsNotification());

// Or using the notification facade
Notification::route('fast2sms', '9999999999')
    ->notify(new SmsNotification());
```
<llm-snippet-file>docs/queues.md</llm-snippet-file>
# Queue Integration

## Configuration

Ensure your queue is configured in `config/queue.php`:

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        // ... other redis configuration
    ],
],
```


## Queueing Messages

```php
// Queue a Quick SMS
Fast2sms::quickQueue('9999999999', 'Queued message');

// Queue a DLT SMS
Fast2sms::dltQueue(
    numbers: '9999999999',
    templateId: 'YOUR_TEMPLATE_ID',
    variablesValues: ['John Doe']
);

// Advanced Queue Options
Fast2sms::to('9999999999')
    ->message('Test message')
    ->onConnection('redis')
    ->onQueue('sms')
    ->delay(now()->addMinutes(10))
    ->queue();
```


## Queue Workers

Start a queue worker:

```shell script
php artisan queue:work --queue=sms
```


## Failed Jobs

Handle failed jobs in `config/queue.php`:

```php
'failed' => [
    'driver' => 'database',
    'database' => 'mysql',
    'table' => 'failed_jobs',
],
```
<llm-snippet-file>docs/events.md</llm-snippet-file>
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
```
<llm-snippet-file>docs/api-reference.md</llm-snippet-file>
