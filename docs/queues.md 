# Queue Integration

## Configuration

Ensure your queue is configured in `config/queue.php`:

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        // ... other redis configuration
    ],
],
```


## Queueing Messages

```php
// Queue a Quick SMS
Fast2sms::quickQueue('9999999999', 'Queued message');

// Queue a DLT SMS
Fast2sms::dltQueue(
    numbers: '9999999999',
    templateId: 'YOUR_TEMPLATE_ID',
    variablesValues: ['John Doe']
);

// Advanced Queue Options
Fast2sms::to('9999999999')
    ->message('Test message')
    ->onConnection('redis')
    ->onQueue('sms')
    ->delay(now()->addMinutes(10))
    ->queue();
```


## Queue Workers

Start a queue worker:

```shell script
php artisan queue:work --queue=sms
```


## Failed Jobs

Handle failed jobs in `config/queue.php`:

```php
'failed' => [
    'driver' => 'database',
    'database' => 'mysql',
    'table' => 'failed_jobs',
],
```
