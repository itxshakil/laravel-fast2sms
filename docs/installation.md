# Installation

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | ^8.3 |
| Laravel | ^11.0 \| ^12.0 \| ^13.0 |
| Fast2SMS account | [Sign up free](https://www.fast2sms.com) |

---

## Step 1 — Install via Composer

```bash
composer require itxshakil/laravel-fast2sms
```

---

## Step 2 — Auto-Discovery

Laravel automatically discovers the service provider and facade. No manual registration is needed for Laravel 11+.

### Manual Registration (optional)

If you have disabled package auto-discovery, add the following to `config/app.php`:

```php
'providers' => [
    // ...
    Shakil\Fast2sms\Fast2smsServiceProvider::class,
],

'aliases' => [
    // ...
    'Fast2sms' => Shakil\Fast2sms\Facades\Fast2sms::class,
],
```

---

## Step 3 — Publish Configuration

```bash
php artisan vendor:publish --tag=fast2sms-config
```

This creates `config/fast2sms.php` in your application.

---

## Step 4 — Set Your API Key

Add your Fast2SMS API key to `.env`:

```env
FAST2SMS_API_KEY=your_api_key_here
```

You can find your API key in the [Fast2SMS developer panel](https://www.fast2sms.com/dashboard/dev-api).

---

## Step 5 — (Optional) Publish Migrations

If you want to log SMS sends to the database, publish and run the migrations:

```bash
php artisan vendor:publish --tag=fast2sms-migrations
php artisan migrate
```

Then enable database logging in `.env`:

```env
FAST2SMS_DATABASE_LOGGING=true
```

---

## Local Development

For local development, use the `log` driver to avoid making real API calls:

```env
FAST2SMS_DRIVER=log
```

All sends will be written to your Laravel log instead of hitting the Fast2SMS API.

---

## See Also

- [Cost-Saving Features](cost-saving-features.md)
- [Configuration](configuration.md)
- [Quick Start](../README.md#quick-start)
