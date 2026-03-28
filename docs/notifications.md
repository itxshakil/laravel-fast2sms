# Laravel Notifications

Laravel Fast2SMS provides two notification channels out of the box: `fast2sms` for SMS and `whatsapp` for WhatsApp.

---

## SMS Notifications

### 1. Add the Channel

Return `'fast2sms'` from your notification's `via()` method:

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;

class OrderShipped extends Notification
{
    public function __construct(private readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['fast2sms'];
    }

    public function toSms(object $notifiable): SmsMessage
    {
        return SmsMessage::create("Your order #{$this->order->id} has shipped!")
            ->withRoute(SmsRoute::QUICK);
    }
}
```

### 2. Add Routing to Your Model

Add `routeNotificationForFast2sms()` to your notifiable model:

```php
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForFast2sms(): string
    {
        return $this->phone_number;
    }
}
```

### 3. Send the Notification

```php
$user->notify(new OrderShipped($order));

// Or via the Notification facade
Notification::send($users, new OrderShipped($order));
```

---

## SmsMessage Builder

```php
SmsMessage::create('Your message here')
    ->withRoute(SmsRoute::QUICK)       // Set SMS route
    ->withNumbers(['9876543210'])      // Override recipient numbers
    ->from('MYAPP');                   // Override sender ID
```

### Deprecated Methods (v1 → v2)

| Old (deprecated) | New |
|-----------------|-----|
| `content('...')` | `withContent('...')` |
| `route(...)` | `withRoute(...)` |
| `to('...')` | `withNumbers('...')` |

---

## WhatsApp Notifications

### 1. Add the Channel

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Notifications\Messages\WhatsAppMessage;

class OrderShipped extends Notification
{
    public function via(object $notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::text("Your order #{$this->order->id} has shipped!");
    }
}
```

### 2. Add Routing to Your Model

```php
public function routeNotificationForWhatsapp(): string
{
    return $this->phone_number;
}
```

---

## WhatsAppMessage Builder

```php
// Text
WhatsAppMessage::text('Hello!');

// Image
WhatsAppMessage::image('https://example.com/image.jpg');

// Document
WhatsAppMessage::document('https://example.com/file.pdf');

// Location
WhatsAppMessage::forLocation(lat: 28.6139, lng: 77.2090);

// Interactive
WhatsAppMessage::forInteractive([/* ... */]);
```

---

## Sending to Multiple Channels

```php
public function via(object $notifiable): array
{
    return ['fast2sms', 'whatsapp', 'mail'];
}
```

---

## See Also

- [Cost-Saving Features](cost-saving-features.md)
- [Queuing](queuing.md)
- [SMS Guide](sms-guide.md)
- [WhatsApp Guide](whatsapp.md)
- [Testing](testing.md)
