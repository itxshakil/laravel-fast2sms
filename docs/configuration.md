# Configuration

After publishing the config file with `php artisan vendor:publish --tag=fast2sms-config`, you will find `config/fast2sms.php` in your application.

---

## All Configuration Keys

### `api_key`

**Env:** `FAST2SMS_API_KEY` | **Default:** `''`

Your Fast2SMS API key. Required for the `api` driver. Get it from the [Fast2SMS developer panel](https://www.fast2sms.com/dashboard/dev-api).

```env
FAST2SMS_API_KEY=your_api_key_here
```

---

### `default_sender_id`

**Env:** `FAST2SMS_DEFAULT_SENDER_ID` | **Default:** `FSTSMS`

The default DLT sender ID used when none is specified per-send. Must be a 6-character alphanumeric string registered with your telecom operator.

---

### `default_route`

**Env:** `FAST2SMS_DEFAULT_ROUTE` | **Default:** `dlt`

The default SMS route. Available values: `dlt`, `quick`, `otp`, `dlt_manual`.

---

### `base_url`

**Default:** `https://www.fast2sms.com/dev`

The Fast2SMS API base URL. Do not change this unless instructed by Fast2SMS support.

---

### `timeout`

**Default:** `30`

HTTP request timeout in seconds. Increase for slow network environments.

---

### `driver`

**Env:** `FAST2SMS_DRIVER` | **Default:** `api`

Controls how messages are sent:

| Value | Behaviour |
|-------|-----------|
| `api` | Sends real HTTP requests to Fast2SMS |
| `log` | Logs the request payload; no HTTP call made |

Use `log` in local and CI environments.

---

### `database_logging`

**Env:** `FAST2SMS_DATABASE_LOGGING` | **Default:** `false`

When `true`, every SMS send (success or failure) is recorded in the database. Requires running the package migrations.

---

### `events.enabled`

**Env:** `FAST2SMS_EVENTS_ENABLED` | **Default:** `true`

Set to `false` to disable all event dispatching (useful in high-throughput scenarios where you don't need events).

---

### `queue.*`

| Key | Env | Default | Description |
|-----|-----|---------|-------------|
| `queue.enabled` | `FAST2SMS_QUEUE_ENABLED` | `false` | Dispatch sends as background jobs |
| `queue.connection` | `FAST2SMS_QUEUE_CONNECTION` | `null` | Queue connection (e.g. `redis`) |
| `queue.name` | `FAST2SMS_QUEUE_NAME` | `default` | Queue name |
| `queue.tries` | `FAST2SMS_QUEUE_TRIES` | `3` | Max job attempts before failing |

---

### `whatsapp.*`

| Key | Env | Description |
|-----|-----|-------------|
| `whatsapp.default_phone_number_id` | `FAST2SMS_WHATSAPP_PHONE_NUMBER_ID` | Your WhatsApp Business phone number ID |
| `whatsapp.default_waba_id` | `FAST2SMS_WHATSAPP_WABA_ID` | Your WhatsApp Business Account ID |
| `whatsapp.version` | `FAST2SMS_WHATSAPP_VERSION` | API version (default: `v24.0`) |

---

### Cost-Saving Features

All cost-saving features are **opt-in** and disabled by default. See [Cost-Saving Features](cost-saving-features.md) for full documentation.

| Key | Env | Default | Description |
|-----|-----|---------|-------------|
| `recipients.deduplicate` | `FAST2SMS_DEDUP_RECIPIENTS` | `true` | Strip duplicate numbers before every send |
| `validation.strip_invalid_recipients` | `FAST2SMS_STRIP_INVALID` | `false` | Remove invalid numbers; throw if all are invalid |
| `deduplication.enabled` | `FAST2SMS_DEDUP_ENABLED` | `false` | Prevent identical sends within a TTL window |
| `deduplication.ttl` | `FAST2SMS_DEDUP_TTL` | `60` | Dedup window in seconds |
| `deduplication.store` | `FAST2SMS_DEDUP_STORE` | `null` | Cache store (null = default) |
| `throttle.enabled` | `FAST2SMS_THROTTLE_ENABLED` | `false` | Enforce per-minute send-rate limit |
| `throttle.max_per_minute` | `FAST2SMS_THROTTLE_MAX` | `60` | Maximum sends per minute |
| `throttle.store` | `FAST2SMS_THROTTLE_STORE` | `null` | Cache store (null = default) |
| `balance_gate.enabled` | `FAST2SMS_BALANCE_GATE` | `false` | Check wallet balance before every send |
| `balance_gate.threshold` | `FAST2SMS_BALANCE_THRESHOLD` | `10.0` | Minimum balance in ₹ before blocking |
| `balance_gate.abort` | `FAST2SMS_BALANCE_ABORT` | `true` | Throw `InsufficientBalanceException` when below threshold |
| `recipients.batch_size` | `FAST2SMS_BATCH_SIZE` | `0` | Split large lists into chunks (0 = disabled) |

---

## Example `.env`

```env
FAST2SMS_API_KEY=your_api_key_here
FAST2SMS_DEFAULT_SENDER_ID=MYAPP
FAST2SMS_DEFAULT_ROUTE=dlt
FAST2SMS_DRIVER=api
FAST2SMS_DATABASE_LOGGING=false
FAST2SMS_BALANCE_THRESHOLD=500
FAST2SMS_EVENTS_ENABLED=true
FAST2SMS_QUEUE_ENABLED=true
FAST2SMS_QUEUE_CONNECTION=redis
FAST2SMS_QUEUE_NAME=sms
FAST2SMS_QUEUE_TRIES=3
FAST2SMS_WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
FAST2SMS_WHATSAPP_WABA_ID=your_waba_id

# Cost-saving features (all opt-in)
FAST2SMS_DEDUP_RECIPIENTS=true
FAST2SMS_STRIP_INVALID=true
FAST2SMS_DEDUP_ENABLED=true
FAST2SMS_DEDUP_TTL=60
FAST2SMS_THROTTLE_ENABLED=true
FAST2SMS_THROTTLE_MAX=60
FAST2SMS_BALANCE_GATE=true
FAST2SMS_BALANCE_ABORT=true
```

---

## See Also

- [Installation](installation.md)
- [Sending SMS](sms-guide.md)
- [Queuing](queuing.md)
- [Cost-Saving Features](cost-saving-features.md)
- [API Reference](api-reference.md)
