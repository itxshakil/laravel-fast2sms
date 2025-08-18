# Laravel Fast2sms 📲

[![Latest Version](https://img.shields.io/packagist/v/itxshakil/laravel-fast2sms.svg?style=flat-square)](https://packagist.org/packages/itxshakil/laravel-fast2sms)
[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A **Laravel package** for sending SMS using the [Fast2sms API](https://www.fast2sms.com/) with a **fluent, expressive interface**.  
Supports **Quick SMS**, **DLT templates**, **OTP**, queueing, scheduling, and balance checks.

---

## Table of Contents

- [Requirements](#requirements)
- [Features](#features)
- [Quick Start Guide](#quick-start-guide)
- [Installation](#installation)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [API Methods](#api-methods)
- [Error Handling](#exceptions)
- [Advanced Features](#advanced-features)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [License](#license)

---

## Requirements

- **PHP 8.3 or higher**
- **Laravel 12 or higher** (the package relies on `illuminate/support` v12+)

---

## ✨ Features

- **Fluent Interface:** Chainable API for building and sending SMS.
- **Multiple Routes:** Supports **Quick SMS**, **DLT SMS**, and **OTP SMS**.
- **Queue Support:** Built-in job queueing for asynchronous processing.
- **Easy Configuration:** Simple config file and environment variable setup.
- **DLT Compliant:** Send DLT messages with templates and variables.
- **Service & Facade:** Use the `Fast2sms` service directly or via facade.
- **API Helpers:** Check wallet balance and DLT details.
- **Artisan Commands:** Publish configuration and monitor balance.

---

## 🚀 Quick Start Guide

1. **Install via Composer:**
    ```bash
    composer require itxshakil/laravel-fast2sms
    ```

2. **Publish Configuration:**
    ```bash
    php artisan vendor:publish --tag=fast2sms-config
    ```
   This creates `fast2sms.php` in your `config` directory.

3. **Update Environment Variables:**
   Add to your `.env`:
    ```ini
    FAST2SMS_API_KEY="YOUR_API_KEY"
    FAST2SMS_DEFAULT_SENDER_ID="FSTSMS"
    FAST2SMS_DEFAULT_ROUTE="dlt"
    ```

4. **Send Your First DLT SMS:**
    ```php
    use Shakil\Fast2sms\Facades\Fast2sms;

    Fast2sms::dlt(
        numbers: '9999999999',
        templateId: 'YOUR_TEMPLATE_ID',
        variablesValues: ['John Doe'],
        senderId: 'YOUR_SENDER_ID'
    );
    ```

---

## ⚙️ Installation

Install the package via Composer:

```bash
composer require itxshakil/laravel-fast2sms
```
Supports **Laravel auto-discovery**. No manual provider registration required.

---

## 🛠️ Configuration

**Publish the configuration file:**
```bash
php artisan vendor:publish --tag=fast2sms-config
```
Creates `fast2sms.php` in your `config` directory.

**Environment Variables:**
Update your `.env` file:
```ini
FAST2SMS_API_KEY="YOUR_API_KEY"
FAST2SMS_DEFAULT_SENDER_ID="FSTSMS"
FAST2SMS_DEFAULT_ROUTE="dlt"
```

---

## 📝 Basic Usage

Use the **`Fast2sms` facade** for convenience. Three primary sending methods, each with a dedicated helper.

### Quick SMS

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\SmsLanguage;

Fast2sms::quick('9999999999', 'Hello, this is a Quick SMS!');
Fast2sms::quick('9999999999', 'नमस्ते! यह एक क्विक एसएमएस है।', SmsLanguage::UNICODE);
```

### DLT SMS

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::dlt(
    numbers: '9999999999',
    templateId: 'YOUR_TEMPLATE_ID',
    variablesValues: ['John Doe'],
    senderId: 'YOUR_SENDER_ID'
);
```

### OTP SMS

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::otp('9999999999', '123456');
```

### Fluent Interface

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

### Check Wallet Balance

```php
use Shakil\Fast2sms\Facades\Fast2sms;

$response = Fast2sms::checkBalance();

if ($response->success()) {
    echo "Wallet Balance: {$response->balance}\n";
    echo "SMS Count: {$response->smsCount}\n";
}
```

### Check DLT Manager Details

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\DltManagerType;
use Shakil\Fast2sms\Responses\DltManagerResponse;

// Get DLT sender IDs
/** @var DltManagerResponse $sendersResponse */
$sendersResponse = Fast2sms::dltManager(DltManagerType::SENDER);

foreach ($sendersResponse->getSenders() as $sender) {
    echo "Sender ID: {$sender['sender_id']} | Entity ID: {$sender['entity_id']}\n";
}
```

---

## 📚 API Methods

| Method | Description |
|---|---|
| `->to(string|array $numbers)` | Sets recipient mobile number(s). |
| `->message(string $message)` | Sets message content (DLT: template ID). |
| `->senderId(string $senderId)` | Sets DLT-approved sender ID. |
| `->route(SmsRoute $route)` | Sets SMS route (`DLT`, `QUICK`, `OTP`, etc.). |
| `->entityId(string $entityId)` | Sets DLT Principal Entity ID. |
| `->templateId(string $templateId)` | Sets DLT Content Template ID. |
| `->variables(array|string $values)` | Sets pipe-separated variable values for DLT template. |
| `->flash(bool $flash)` | Toggles flash message. |
| `->language(SmsLanguage $language)` | Sets message language (`ENGLISH`, `UNICODE`). |
| `->schedule(string|DateTimeInterface $time)` | Schedules SMS at a specific time. |
| `->send()` | Sends SMS with configured parameters. |
| `->quick(...)` | Quick helper to send simple SMS. |
| `->dlt(...)` | Helper for DLT messages. |
| `->otp(...)` | Helper for OTP messages. |
| `->checkBalance()` | Retrieves wallet balance. |
| `->dltManager(string $type)` | Retrieves DLT manager details for `sender` or `template`. |

---

## ⚠️ Exceptions

All errors throw `Fast2smsException`.  
Catch them when handling SMS sending:

```php
use Shakil\Fast2sms\Exceptions\Fast2smsException;

try {
    Fast2sms::quick('9999999999', 'Hello World');
} catch (Fast2smsException $e) {
    logger()->error("SMS failed: " . $e->getMessage());
}
```

---

## 🧩 Advanced Features

### 🚀 Queue Integration

Supports Laravel's queue system for asynchronous SMS sending.

**Queue Configuration:**
```php
// config/queue.php 
'connections' => [ 
    'redis' => [ 
        'driver' => 'redis', 
        // ... other redis configuration 
    ], 
],
```

**Queueing SMS Messages:**
```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Queue a Quick SMS
Fast2sms::quickQueue('9999999999', 'Hello from queue!');

// Queue a DLT SMS
Fast2sms::dltQueue(
    numbers: '9999999999',
    templateId: 'YOUR_TEMPLATE_ID',
    variablesValues: ['John Doe'],
    senderId: 'YOUR_SENDER_ID'
);

// Queue an OTP SMS
Fast2sms::otpQueue('9999999999', '123456');
```

**Advanced Queue Options:**
```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\SmsRoute;

Fast2sms::to('9999999999')
    ->message('Test message')
    ->route(SmsRoute::QUICK)
    ->onConnection('redis')   // Specify queue connection
    ->onQueue('sms')          // Specify queue name
    ->delay(now()->addMinutes(10)) // Add delay
    ->queue();                // Queue the SMS
```

**Queue Methods:**

| Method | Description |
|---|---|
| `->queue()` | Queue SMS using default settings |
| `->onConnection(string $name)` | Set queue connection |
| `->onQueue(string $queue)` | Set queue name |
| `->delay($delay)` | Set delay before processing |
| `->quickQueue()` | Queue Quick SMS |
| `->dltQueue()` | Queue DLT SMS |
| `->otpQueue()` | Queue OTP SMS |

---

### 📱 Notifications Channel

Use Fast2sms as a notification channel in your Laravel applications:

**Create a Notification:**
```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\SmsRoute;
class LowSmsBalanceNotification extends Notification
{
    public function __construct(
        protected float $balance,
        protected float $threshold
    ) {}

    public function via($notifiable)
    {
        return ['fast2sms'];
    }

    public function toFast2sms($notifiable)
    {
        return Fast2sms::to($notifiable->phone)
            ->message("Low SMS balance: {$this->balance}. Threshold: {$this->threshold}.")
            ->route(SmsRoute::QUICK)
            ->send();
    }
}
```
**Use route in SMS Notification:**
```php
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
class User extends Model
{
    use Notifiable, RoutesNotifications;

    protected $fillable = ['name', 'email', 'phone'];

    public function routeNotificationForFast2sms()
    {
        return $this->phone; // Return the phone number for Fast2sms
    }
}
```

**Send the notification:**
```php
use App\Notifications\LowSmsBalanceNotification;
use Illuminate\Support\Facades\Notification;
Notification::route('fast2sms', '9999999999')
    ->notify(new LowSmsBalanceNotification(500, 1000));
```

**Model Setup:**
Ensure your model has a `phone` attribute:
```php
use Illuminate\Database\Eloquent\Model;
class User extends Model
{
    protected $fillable = ['name', 'email', 'phone'];
}
```
---

**Schedule the command:**
```bash
php artisan sms:monitor --threshold=500
```

If no threshold is specified, the value from your configuration file will be used:

```php
// config/fast2sms.php
'balance_threshold' => 1000,
```

**Listen for the event in `AppServiceProvider`:**
```php
use App\Notifications\LowSmsBalanceNotification;
use Illuminate\Support\Facades/Event;
use Illuminate\Support\Facades\Notification;
use Shakil\Fast2sms\Events\LowBalanceDetected;

public function boot(): void
{
    Event::listen(function (LowBalanceDetected $event) {
        Notification::route('mail', 'dev@example.com')
            ->notify(new LowSmsBalanceNotification(
                $event->balance,
                $event->threshold
            ));
    });
}
```

**Example schedule in `App\Console\Kernel`:**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('sms:monitor')->hourly();
}
```

---


## 📚 Documentation

Learn how to use Laravel Fast2sms effectively:

### Getting Started
- [Installation and Requirements](docs/installation.md)
- [Configuration](docs/configuration.md)
- [Basic Usage](docs/basic-usage.md)

### Features
- [API Reference](docs/api-reference.md)
- [Advanced Usage](docs/advanced-usage.md)
- [Queue Integration](docs/queues.md)
- [Notifications](docs/notifications.md)
- [Events & Listeners](docs/events.md)

---

## 🤝 Contributing

Contributions are always welcome!  
Open an issue or submit a pull request for bugs or suggestions.

---

## 📄 License

This package is open-source software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
