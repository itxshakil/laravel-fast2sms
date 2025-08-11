### Fast2sms Laravel Package

A robust and simple-to-use Laravel package for sending SMS messages via the **Fast2sms API**. This package offers a powerful, fluent interface that simplifies sending different types of SMS, including **Quick**, **DLT**, and **OTP** messages.

-----

### ✨ Features

* **Fluent Interface:** Build and send SMS messages with a concise, chainable API.
* **Multiple Routes:** Full support for **Quick SMS**, **DLT SMS**, and **OTP SMS**.
* **Easy Configuration:** Use a simple configuration file and environment variables to set up your API key and default settings.
* **DLT Compliant:** Dedicated methods for sending DLT (Distributed Ledger Technology) messages with support for templates and variables.
* **Service & Facade:** Use the `Fast2sms` service directly or through a convenient facade.
* **API Helpers:** Methods to check your Fast2sms wallet balance and retrieve DLT sender/template details.
* **Artisan Command:** Quickly publish the configuration file with a single command.

-----

### ⚙️ Installation

Install the package via Composer:

```bash
composer require itxshakil/laravel-fast2sms
```

#### Configuration

Publish the configuration file using the Artisan command:

```bash
php artisan vendor:publish --tag=fast2sms-config
```

This will create a `fast2sms.php` file in your `config` directory.

#### Environment Variables

Update your `.env` file with your Fast2sms API key and other defaults:

```ini
FAST2SMS_API_KEY="YOUR_API_KEY"
FAST2SMS_DEFAULT_SENDER_ID="FSTSMS"
FAST2SMS_DEFAULT_ROUTE="dlt"
```

-----

### 📝 Usage

You can use the **`Fast2sms` facade** for convenience. The package supports three primary sending methods, each with a dedicated helper function.

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
```

#### Quick SMS

Quickly send a simple message. This route uses a random sender ID and is not DLT compliant.

```php
Fast2sms::quick('9999999999', 'Hello, this is a Quick SMS!');

// Send with a Unicode message
Fast2sms::quick('9999999999', 'नमस्ते! यह एक क्विक एसएमएस है।', SmsLanguage::UNICODE);
```

#### DLT SMS

Send a DLT-approved message with a template and variables.

```php
Fast2sms::dlt(
    numbers: '9999999999',
    templateId: 'YOUR_TEMPLATE_ID',
    variablesValues: ['John Doe'],
    senderId: 'YOUR_SENDER_ID'
);
```

#### OTP SMS

Send a one-time password.

```php
Fast2sms::otp('9999999999', '123456');
```

#### Fluent Interface

For more control, you can build your message step-by-step.

```php
Fast2sms::to('9999999999')
    ->route(SmsRoute::DLT)
    ->senderId('YOUR_SENDER_ID')
    ->templateId('YOUR_TEMPLATE_ID')
    ->variables(['John Doe'])
    ->send();
```

#### Check Wallet Balance

Retrieve your current wallet balance and available SMS count. The response object provides properties for easy access to the data.

```php
$response = Fast2sms::checkBalance();

if ($response->success()) {
    echo "Wallet Balance: {$response->balance}\n";
    echo "SMS Count: {$response->smsCount}\n";
}
```

#### Check DLT Manager Details

Get details about your DLT sender IDs or templates. The response object includes helper methods to parse the data.

```php
use Shakil\Fast2sms\Responses\DltManagerResponse;

// Get DLT sender IDs
/** @var DltManagerResponse $sendersResponse */
$sendersResponse = Fast2sms::dltManager('sender');

foreach ($sendersResponse->getSenders() as $sender) {
    echo "Sender ID: {$sender['sender_id']} | Entity ID: {$sender['entity_id']}\n";
}
```

-----

### 📚 API Methods

| Method | Description |
|---|---|
| `->to(string\|array $numbers)` | Sets the recipient mobile number(s). |
| `->message(string $message)` | Sets the message content. For DLT, this is the template ID. |
| `->senderId(string $senderId)` | Sets the DLT-approved sender ID. |
| `->route(SmsRoute $route)` | Sets the SMS route (`DLT`, `QUICK`, `OTP`, etc.). |
| `->entityId(string $entityId)` | Sets the DLT Principal Entity ID. |
| `->templateId(string $templateId)` | Sets the DLT Content Template ID. |
| `->variables(array\|string $values)` | Sets the pipe-separated variable values for a DLT template. |
| `->flash(bool $flash)` | Toggles sending a flash message. |
| `->language(SmsLanguage $language)` | Sets the message language (`ENGLISH`, `UNICODE`). |
| `->schedule(string\|DateTimeInterface $time)` | Schedules the SMS to be sent at a specific time. |
| `->send()` | Sends the SMS using the configured parameters. |
| `->quick(...)` | A quick helper method to send a simple SMS. |
| `->dlt(...)` | A quick helper method for DLT messages. |
| `->otp(...)` | A quick helper method for OTP messages. |
| `->checkBalance()` | Retrieves the Fast2sms wallet balance. |
| `->dltManager(string $type)` | Retrieves DLT manager details for `sender` or `template` types. |

-----

### 🤝 Contributing

Contributions are always welcome\! Feel free to open an issue or submit a pull request if you find a bug or have a suggestion.

-----

### 📄 License

This package is open-source software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
