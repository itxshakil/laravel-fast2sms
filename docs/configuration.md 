# Configuration

## Environment Variables

The package uses the following environment variables:
```env
FAST2SMS_API_KEY=your-api-key
FAST2SMS_DEFAULT_SENDER_ID=FSTSMS
FAST2SMS_DEFAULT_ROUTE=dlt
```
## Configuration File

After publishing, you can find the configuration file at `config/fast2sms.php`:
```php
return [
    'api_key' => env('FAST2SMS_API_KEY'),
    'default_route' => env('FAST2SMS_DEFAULT_ROUTE', 'dlt'),
    'default_sender_id' => env('FAST2SMS_DEFAULT_SENDER_ID', 'FSTSMS'),
    'balance_threshold' => env('FAST2SMS_BALANCE_THRESHOLD', 1000),
];
```
### Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `api_key` | Your Fast2SMS API key | - |
| `default_route` | Default SMS route (dlt/quick/otp) | 'dlt' |
| `default_sender_id` | Default sender ID | 'FSTSMS' |
| `balance_threshold` | Balance threshold for notifications | 1000 |

<llm-snippet-file>docs/api-reference.md</llm-snippet-file>
