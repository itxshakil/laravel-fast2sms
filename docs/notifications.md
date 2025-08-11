# Laravel Notifications

## Setup

Add the notification channel to your notifiable model:

```php
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForFast2sms()
    {
        return $this->phone;
    }
}
```


## Creating SMS Notifications

```php
use Illuminate\Notifications\Notification;
use Shakil\Fast2sms\Facades\Fast2sms;

class SmsNotification extends Notification
{
    public function via($notifiable)
    {
        return ['fast2sms'];
    }

    public function toFast2sms($notifiable)
    {
        return Fast2sms::to($notifiable->phone)
            ->message('Your notification message')
            ->send();
    }
}
```


## Sending Notifications

```php
$user->notify(new SmsNotification());

// Or using the notification facade
Notification::route('fast2sms', '9999999999')
    ->notify(new SmsNotification());
```
