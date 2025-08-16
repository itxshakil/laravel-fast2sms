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
