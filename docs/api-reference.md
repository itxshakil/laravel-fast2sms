# API Reference

## Available Methods

### Quick Messages
```php
// Send quick message
Fast2sms::quick(string|array $numbers, string $message): ResponseInterface
Fast2sms::quickQueue(string|array $numbers, string $message): void
```
### DLT Messages
```php
Fast2sms::dlt(
    string|array $numbers,
    string $templateId,
    array $variablesValues,
    ?string $senderId = null
): ResponseInterface

Fast2sms::dltQueue(
    string|array $numbers,
    string $templateId,
    array $variablesValues,
    ?string $senderId = null
): void
```
### OTP Messages
```php
Fast2sms::otp(string|array $numbers, string $otp): ResponseInterface
Fast2sms::otpQueue(string|array $numbers, string $otp): void
```
### Fluent Interface
```php
Fast2sms::to(string|array $numbers)          // Set recipient number(s)
    ->message(string $message)               // Set message content
    ->route(SmsRoute $route)                 // Set SMS route
    ->senderId(?string $senderId)            // Set sender ID
    ->templateId(?string $templateId)        // Set template ID for DLT
    ->variables(array $values)               // Set template variables
    ->flash(bool $flash = true)              // Set as flash message
    ->language(SmsLanguage $language)        // Set message language
    ->schedule(DateTimeInterface $time)      // Schedule message
    ->send(): ResponseInterface              // Send message

// Queue options
->onConnection(string $connection)       // Set queue connection
->onQueue(string $queue)                 // Set queue name
->delay(DateTimeInterface|int $delay)    // Set delay
->queue(): void                          // Queue message
```
### Utility Methods
```php
Fast2sms::checkBalance(): BalanceResponse
Fast2sms::dltManager(DltManagerType $type): DltManagerResponse
```
## Enums

### SmsRoute
```php
SmsRoute::QUICK    // Quick SMS route
SmsRoute::DLT      // DLT SMS route
SmsRoute::OTP      // OTP SMS route
```
### SmsLanguage
```php
SmsLanguage::ENGLISH   // English language
SmsLanguage::UNICODE   // Unicode for regional languages
```
### DltManagerType
```php
DltManagerType::SENDER     // DLT sender IDs
DltManagerType::TEMPLATE   // DLT templates
```
## Response Objects

### ResponseInterface
```php
interface ResponseInterface
{
    public function success(): bool
    public function getMessage(): string
    public function getRequestId(): string
    public function toArray(): array
}
```
### BalanceResponse
```php
public function getBalance(): float
public function getSmsCount(): int
```
### DltManagerResponse
```php
public function getSenders(): array    // List of sender IDs
public function getTemplates(): array  // List of templates
```
