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
