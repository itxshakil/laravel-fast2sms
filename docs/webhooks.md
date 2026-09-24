# Delivery-Status Webhooks

Fast2SMS can POST real-time delivery reports (DLR) to your application as messages move from *accepted* to *delivered* or *failed*. This package ships an opt-in receiver that verifies the request, dispatches typed events, and reconciles your `fast2sms_logs` table so you always know whether a message actually landed — and what it cost.

## How it works

```
Fast2SMS  ──POST──▶  {path}/{secret}  ──▶  VerifyFast2smsWebhook  ──▶  WebhookController
                                                                          │
                                                     WebhookHandler::handle()
                                                                          │
                                        DeliveryStatusResponse ──▶ MessageDelivered / MessageFailed
                                                                          │
                                                            LogDeliveryStatus (reconciles fast2sms_logs)
```

## Setup

1. Publish and run the migrations (the delivery columns are added to `fast2sms_logs`):

   ```bash
   php artisan vendor:publish --tag=fast2sms-migrations
   php artisan migrate
   ```

2. Enable the webhook and set a secret in your `.env`:

   ```dotenv
   FAST2SMS_WEBHOOK_ENABLED=true
   FAST2SMS_WEBHOOK_SECRET=a-long-random-string
   FAST2SMS_DATABASE_LOGGING=true
   ```

3. In the Fast2SMS dashboard (**Create Webhook**), choose the **Standard Webhook** format and point the URL at:

   ```
   https://your-app.com/fast2sms/webhook/a-long-random-string
   ```

That's it. Delivered and failed reports now flow into your app.

## Security

Fast2SMS does **not** sign webhook requests, so authenticity relies on a shared secret embedded in the URL. Treat the secret like a credential:

- The endpoint rejects **every** request until `FAST2SMS_WEBHOOK_SECRET` is set.
- The secret is compared in constant time (`hash_equals`).
- The secret can also be sent as an `X-Webhook-Secret` header or `?secret=` query parameter instead of the path segment.
- Optionally restrict by source IP: `FAST2SMS_WEBHOOK_ALLOWED_IPS=1.2.3.4,5.6.7.8`.
- The route runs under the `api` middleware group, so it is exempt from CSRF by default.

## Listening for events

```php
use Shakil\Fast2sms\Events\MessageDelivered;
use Shakil\Fast2sms\Events\MessageFailed;

class NotifyOnDeliveryFailure
{
    public function handle(MessageFailed $event): void
    {
        $status = $event->response;

        report("SMS {$status->requestId} to {$status->mobile} failed: {$status->getMessage()}");
    }
}
```

Both events carry a `DeliveryStatusResponse`:

| Property | Description |
|----------|-------------|
| `status` | `DeliveryStatus` enum: `Delivered`, `Failed`, or `Pending` |
| `requestId` | The `request_id` returned when the message was sent |
| `mobile` | Recipient number |
| `channel` | `sms`, `whatsapp`, or `rcs` |
| `failureReason` | Error text when failed |
| `amountDebited` | Balance debited for the message |
| `deliveryTimestamp` | Unix timestamp of delivery |
| `postAttempt` | Webhook retry counter (used for idempotency) |

`isSuccess()` returns `true` only for a delivered message; `getRawData()` exposes the full payload.

## Log reconciliation

When `database_logging` and `webhook.update_logs` are both enabled, each report updates the matching `fast2sms_logs` row (matched by `request_id`), setting `status`, `is_success`, `failure_reason`, `amount_debited`, `delivery_timestamp`, and `post_attempt`. If no row exists yet, one is created. Stale retries (a lower `post_attempt` or an older `delivery_timestamp`) are ignored, so out-of-order deliveries never clobber newer state.

## Bring your own route

If you'd rather control the route yourself (custom auth, versioned API, queue-first ingestion), disable auto-routing and call the handler directly:

```dotenv
FAST2SMS_WEBHOOK_AUTO_ROUTE=false
```

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Route::post('/hooks/sms', function (Request $request) {
    $status = Fast2sms::webhook()->handle($request);

    return response()->json(['ok' => $status->isSuccess()]);
});
```

## Testing

With `Fast2sms::fake()` active, handled webhooks are recorded and can be asserted on:

```php
Fast2sms::fake();

$this->postJson('/fast2sms/webhook/' . config('fast2sms.webhook.secret'), [
    'request_id' => 'req_123',
    'status' => 'delivered',
]);

Fast2sms::assertMessageDelivered('req_123');
Fast2sms::assertWebhookHandledCount(1);
```

See [testing.md](./testing.md#webhook-assertions) for the full list of webhook assertions.

## CleverTap format

If you configure the **CleverTap (SMS)** webhook template in the dashboard, its `statuses[]` envelope is flattened automatically onto the standard shape, so events and reconciliation work unchanged.
