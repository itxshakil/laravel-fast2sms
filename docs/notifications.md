# Laravel Notifications

The package provides dedicated notification channels for both SMS and WhatsApp, allowing you to easily integrate messaging into your Laravel application's notification system.

## SMS Notifications

### Model Setup

Add the `routeNotificationForSms` method to your notifiable model:

```php
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForSms()
    {
        return $this->phone;
    }
}
```

### Creating SMS Notifications

You can return a simple string or use the `SmsMessage` builder for more control.

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;
use Shakil\Fast2sms\Enums\SmsRoute;

class OrderShipped extends Notification
{
    public function via($notifiable)
    {
        return ['fast2sms'];
    }

    public function toSms($notifiable)
    {
        // Simple string
        // return "Your order #{$notifiable->order_id} has been shipped!";

        // Fluent builder
        return (new SmsMessage)
            ->route(SmsRoute::DLT)
            ->template('ORDER_SHIPPED_01', [$notifiable->order_id])
            ->from('FSTSMS');
    }
}
```

---

## WhatsApp Notifications

### Model Setup

Add the `routeNotificationForWhatsApp` method to your notifiable model:

```php
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForWhatsApp()
    {
        return $this->phone; // Should include country code, e.g., 919876543210
    }
}
```

### Creating WhatsApp Notifications

Use the `WhatsAppMessage` builder to send session or template messages.

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Notifications\Messages\WhatsAppMessage;
use Shakil\Fast2sms\Enums\WhatsAppType;

class WelcomeNotification extends Notification
{
    public function via($notifiable)
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable)
    {
        // Simple string (Session Message)
        // return "Welcome to our platform, {$notifiable->name}!";

        // Template Message
        return (new WhatsAppMessage)
            ->template('WELCOME_USER_01', [$notifiable->name]);
            
        // Advanced: Interactive Message
        /*
        return (new WhatsAppMessage)
            ->interactive([
                'type' => 'button',
                'body' => ['text' => 'Are you interested?'],
                'action' => [
                    'buttons' => [
                        ['type' => 'reply', 'reply' => ['id' => 'yes', 'title' => 'Yes']],
                        ['type' => 'reply', 'reply' => ['id' => 'no', 'title' => 'No']],
                    ]
                ]
            ]);
        */
    }
}
```

## Sending Notifications

```php
$user->notify(new WelcomeNotification());

// Or using the Notification facade
use Illuminate\Support\Facades\Notification;

Notification::route('whatsapp', '919876543210')
    ->notify(new WelcomeNotification());
```
