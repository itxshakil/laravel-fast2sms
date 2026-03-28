# SMS Guide

This guide covers all SMS sending options available in Laravel Fast2SMS.

---

## Routes Overview

| Route | Enum | Use Case |
|-------|------|----------|
| Quick | `SmsRoute::QUICK` | Promotional / general messages |
| OTP | `SmsRoute::OTP` | One-time passwords |
| DLT | `SmsRoute::DLT` | Transactional messages (DLT registered) |
| DLT Manual | `SmsRoute::DLT_MANUAL` | DLT with manual template variables |

---

## Quick SMS

Send a simple message without DLT registration:

```php
use Shakil\Fast2sms\Facades\Fast2sms;

$response = Fast2sms::quick(
    numbers: '9876543210',
    message: 'Hello from Fast2SMS!',
);
```

---

## OTP SMS

Send a one-time password using a pre-approved OTP template:

```php
$response = Fast2sms::otp(
    numbers: '9876543210',
    otpValue: '123456',
);
```

---

## DLT SMS

Send a DLT-registered transactional message:

```php
$response = Fast2sms::dlt(
    numbers: ['9876543210', '9123456789'],
    templateId: 'your_dlt_template_id',
    variablesValues: 'Your order #1234 has been shipped.',
    senderId: 'MYSHOP',
);
```

### DLT with Variables

```php
$response = Fast2sms::dlt(
    numbers: '9876543210',
    templateId: 'your_template_id',
    variablesValues: ['John', '654321'],
    senderId: 'MYAPP',
);
```

---

## Flash SMS

Flash SMS appears directly on the recipient's screen without being stored:

```php
$response = Fast2sms::to('9876543210')
    ->message('This is a flash message!')
    ->flash()
    ->send();
```

---

## Multiple Recipients

Pass an array of numbers to send to multiple recipients in one API call:

```php
$response = Fast2sms::quick(
    numbers: ['9876543210', '9123456789', '9000000001'],
    message: 'Broadcast message to all users',
);
```

> **Note:** Fast2SMS supports up to 1000 numbers per API call.

---

## Checking the Response

All send methods return an `SmsResponse` object:

```php
$response = Fast2sms::quick(numbers: '9876543210', message: 'Hello!');

if ($response->isSuccess()) {
    echo 'Sent! Request ID: ' . $response->requestId;
} else {
    echo 'Failed: ' . $response->message;
}
```

### `SmsResponse` Properties

| Property | Type | Description |
|----------|------|-------------|
| `isSuccess()` | `bool` | Whether the send was accepted |
| `requestId` | `string\|null` | Unique request ID from Fast2SMS |
| `message` | `string` | API response message |
| `data` | `array` | Raw response data |

---

## Using SmsMessage Builder

For notification-style usage, use the fluent `SmsMessage` builder:

```php
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;

$message = SmsMessage::create('Your OTP is 123456')
    ->withRoute(SmsRoute::QUICK)
    ->withNumbers(['9876543210', '9123456789']);
```

---

## Wallet Balance

Check your remaining SMS balance:

```php
$balance = Fast2sms::checkBalance();

echo 'Balance: ₹' . $balance->balance;
```

---

## DLT Manager Details

Retrieve your DLT registration details:

```php
use Shakil\Fast2sms\Enums\DltManagerType;

$dlt = Fast2sms::dltManager(DltManagerType::SENDER);
```

---

## See Also

- [Cost-Saving Features](cost-saving-features.md) — deduplication, throttling, balance gate, and more
- [Notifications](notifications.md)
- [Queuing](queuing.md)
- [Error Handling](error-handling.md)
