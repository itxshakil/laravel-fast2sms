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
