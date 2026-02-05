# WhatsApp Support

The Laravel Fast2sms package provides full integration with the Fast2sms WhatsApp API. You can send session messages (simple or Meta format), template messages, and manage your WhatsApp Business Account (WABA) and templates.

## Basic Usage

The best way to send WhatsApp messages is through the fluent interface provided by the `Fast2sms` facade.

### Sending Text Messages

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->sendText('Hello from WhatsApp!');
```

### Sending Media Messages

```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Send an image
Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->sendImage('https://example.com/image.jpg', 'Optional caption');

// Send a document
Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->sendDocument('https://example.com/invoice.pdf', 'invoice.pdf', 'Your invoice');
```

### Sending Template Messages

Template messages allow you to send pre-approved messages to users.

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->template('WELCOME_USER')
    ->variables(['John Doe'])
    ->send();
```

### Sending Session Messages (Fluent)

You can also use a fluent builder for session messages.

```php
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Enums\WhatsAppType;

Fast2sms::viaWhatsApp('919876543210')
    ->type(WhatsAppType::TEXT)
    ->body('Hello there!')
    ->send();

Fast2sms::viaWhatsApp('919876543210')
    ->type(WhatsAppType::IMAGE)
    ->media('https://example.com/cat.jpg')
    ->body('Look at this cat')
    ->send();
```

## Advanced Features

### Meta Format (Advanced Session Messages)

If you need full control over the message payload (for interactive messages, locations, etc.), you can use the Meta format.

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::whatsapp()->sendMetaMessage('919876543210', [
    'messaging_product' => 'whatsapp',
    'type' => 'interactive',
    'interactive' => [
        'type' => 'button',
        'body' => ['text' => 'Would you like to continue?'],
        'action' => [
            'buttons' => [
                ['type' => 'reply', 'reply' => ['id' => 'yes', 'title' => 'Yes']],
                ['type' => 'reply', 'reply' => ['id' => 'no', 'title' => 'No']],
            ]
        ]
    ]
]);
```

### Management API

You can manage your templates and retrieve WABA details directly.

```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Get WABA number details
$numbers = Fast2sms::whatsapp()->getWabaDetails('number');

// Get all templates
$templates = Fast2sms::whatsapp()->manageTemplates('GET');
```

## Testing & Faking

The package provides built-in support for faking WhatsApp messages in your tests.

```php
use Shakil\Fast2sms\Facades\Fast2sms;

public function test_it_sends_whatsapp_message()
{
    Fast2sms::fake();

    Fast2sms::viaWhatsApp()
        ->to('919876543210')
        ->sendText('Test message');

    Fast2sms::assertSent(function ($payload) {
        return $payload['to'] === '919876543210' && 
               $payload['type'] === 'text';
    });
}
```

## Asynchronous Sending (Queueing)

You can easily queue your WhatsApp messages to improve performance.

```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->template('WELCOME_USER')
    ->variables(['John Doe'])
    ->queue();

// With custom queue configuration
Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->sendText('Delayed Message')
    ->onQueue('high-priority')
    ->delay(60)
    ->queue();
```

## Documentation

### Notifications
See [Notifications Guide](../notifications.md) for using WhatsApp as a Laravel notification channel.
# WhatsApp Messages

The package supports both simplified session messages and the standard Meta API format for more advanced use cases.

## Sending Messages (Fluent API)

The recommended way to send messages is via the `viaWhatsApp()` method on the `Fast2sms` facade.

### Text Message
```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->sendText('Hello World');
```

### Media Messages
```php
// Image
Fast2sms::viaWhatsApp('919876543210')->sendImage('https://example.com/image.jpg', 'Check this out');

// Document
Fast2sms::viaWhatsApp('919876543210')->sendDocument('https://example.com/file.pdf', 'filename.pdf', 'Caption');
```

### Advanced Fluent Builder
You can also use the `type()` and `body()` methods:
```php
use Shakil\Fast2sms\Enums\WhatsAppType;

Fast2sms::viaWhatsApp('919876543210')
    ->type(WhatsAppType::TEXT)
    ->body('Hello World')
    ->send();
```

---

## API Reference (Low-level)

### Send Session Message (Simple)

Send a text or media message using a simplified format.

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp-session`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `phone_number_id` | string | Yes | The ID of the phone number sending the message. |
| `to` | string | Yes | Recipient mobile number (with country code). |

### Headers

| Header | Value |
|--------|-------|
| `authorization` | YOUR_API_KEY |
| `content-type` | `application/json` |

### Body Examples

#### Text Message
```json
{
  "type": "text",
  "text": {
    "body": "Hello World"
  }
}
```

#### Image Message
```json
{
  "type": "image",
  "image": {
    "link": "https://example.com/image.jpg",
    "caption": "Check this out"
  }
}
```

---

## Send Session Message (META Format - Advanced)

Sends a session message within 24 hours of the user's reply using the standard Meta API format.

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/messages`

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `version` | string | Yes | API Version (e.g., `v24.0`). |
| `phone_number_id` | string | Yes | Phone Number ID. |

### Body Example (Text)

```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "919876543210",
  "type": "text",
  "text": {
    "body": "Hello from Meta API"
  }
}
```

### Supported Message Types
- `text`
- `image`
- `document`
- `audio`
- `video`
- `sticker`
- `location`
- `reaction`
- `interactive` (list, quick replies, catalog, etc.)
# WhatsApp Templates

Template messages allow you to send pre-approved messages to users. You can use the simplified API or the advanced Meta format with components.

## Sending Templates (Fluent API)

### Basic Usage
```php
use Shakil\Fast2sms\Facades\Fast2sms;

Fast2sms::viaWhatsApp()
    ->to('919876543210')
    ->template('WELCOME_01')
    ->variables(['John', '1234'])
    ->send();
```

### With Media
If your template has a media header, you can provide the URL:
```php
Fast2sms::viaWhatsApp('919876543210')
    ->template('INVOICE_TEMPLATE')
    ->variables(['John'])
    ->media('https://example.com/invoice.pdf')
    ->documentFilename('invoice_123.pdf')
    ->send();
```

### Advanced Components (Meta Format)
For full control over the template payload (e.g., buttons, specific language codes):
```php
Fast2sms::viaWhatsApp('919876543210')
    ->template('custom_template')
    ->components([
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => 'John'],
            ],
        ],
        [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => '0',
            'parameters' => [
                ['type' => 'text', 'text' => 'track-123'],
            ],
        ],
    ])
    ->send();
```

---

## API Reference (Low-level)

### Send Template Message (Simple)

Simplified API for sending WhatsApp template messages.

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `authorization` | string | Yes | Your API Key. |
| `message_id` | integer | Yes | Fast2SMS Message ID of the template. |
| `phone_number_id` | string | Yes | WABA Phone Number ID. |
| `numbers` | string | Yes | Destination mobile number. |
| `variables_values`| string | No | Variable values joined with `|` (e.g., `John|1234`). |
| `media_url` | uri | No | URL of the media if the template has a Header Media. |
| `document_filename`| string | No | Custom filename for PDF documents. |

### Response Example

```json
{
  "status": true,
  "message": "Message sent successfully",
  "request_id": "6a3b2c1d"
}
```

---

## Template Management

Manage your WhatsApp Business templates.

### Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/dev/whatsapp/{version}/{waba_id}/message_templates` | Create Template |
| `GET`  | `/dev/whatsapp/{version}/{waba_id}/message_templates` | Get All Templates |
| `GET`  | `/dev/whatsapp/{version}/{waba_id}/message_templates/{template_id}` | Get Template by ID |
| `DELETE`| `/dev/whatsapp/{version}/{waba_id}/message_templates` | Delete Template |

### Create Template Example (Text)

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{waba_id}/message_templates`

**Body:**
```json
{
  "name": "welcome_message",
  "category": "MARKETING",
  "language": "en_US",
  "components": [
    {
      "type": "BODY",
      "text": "Hello {{1}}, welcome to our service!"
    }
  ]
}
```

---

## Get WABA & Template Details

Use this API to get WABA ID, Phone Number ID, Message ID, and Meta Template ID.

**Endpoint:** `GET https://www.fast2sms.com/dev/dlt_manager/whatsapp`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `authorization` | string | Yes | Your API Key. |
| `type` | string | Yes | `number` or `template`. |

### Response Example (number)

```json
{
    "success": true,
    "data": [
        {
            "waba_id": 99999999999xxxx,
            "phone_number_id": 576xxxxxx,
            "number": "15xxxxxxxxx",
            "verified_name": "Name",
            "name_status": "Approved",
            "quality_rating": "GREEN",
            "messaging_limit": "TIER_100K",
            "platform_type": "CLOUD_API",
            "connection_status": "CONNECTED"
        }
    ]
}
```
# WABA Management

Manage your WhatsApp Business Account (WABA) profiles, phone numbers, and health status.

## Using the Service

### Business Profile
```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Get Profile
$response = Fast2sms::whatsapp()->getBusinessProfile();

// Update Profile
$response = Fast2sms::whatsapp()->updateBusinessProfile([
    'about' => 'Hello from our business!',
    'description' => 'We provide great services.',
]);
```

### Phone Numbers
```php
// Get all phone numbers
$response = Fast2sms::whatsapp()->getPhoneNumbers();

// Get specific number details
$response = Fast2sms::whatsapp()->getPhoneNumberDetails('PHONE_NUMBER_ID');
```

### WABA Health & Details
```php
// Get Health Status
$response = Fast2sms::whatsapp()->getWabaHealthStatus();

// Get WABA & Template IDs
$response = Fast2sms::whatsapp()->getWabaDetails('number');
```

---

## API Reference (Low-level)

### Business Profile

### Get Business Profile
**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/whatsapp_business_profile`

### Update Business Profile
**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/whatsapp_business_profile`

### Update Business Profile Photo
**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/whatsapp_business_profile_photo`

---

## Phone Numbers

### Get Phone Numbers
**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{waba_id}/phone_numbers`

### Get Single Phone Number Details
**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}`

---

## WABA Details

### Get WABA & Template Details
**Endpoint:** `GET https://www.fast2sms.com/dev/dlt_manager/whatsapp`

### Get WABA Health Status
**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{waba_id}`
# Block/Unblock WhatsApp User

Manage blocked users for your WhatsApp Business Account.

## Using the Service

### Block User(s)
```php
use Shakil\Fast2sms\Facades\Fast2sms;

// Single number
Fast2sms::whatsapp()->block('919876543210');

// Multiple numbers
Fast2sms::whatsapp()->block(['919876543210', '919876543211']);
```

### Unblock User(s)
```php
Fast2sms::whatsapp()->unblock('919876543210');
```

### Get Blocked Users List
```php
$response = Fast2sms::whatsapp()->getBlockedUsers();

if ($response->isSuccess()) {
    $blockedUsers = $response->data;
}
```

---

## API Reference (Low-level)

### Get Blocked Users

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/block_users`

### Parameters

| Type | Parameter | Data Type | Required | Description |
|------|-----------|-----------|----------|-------------|
| Path | `version` | string | Yes | Defaults to `v24.0`. |
| Path | `phone_number_id` | string | Yes | The ID of the phone number. |
| Query | `authorization` | string | Yes | Your API Key. |

### Response Example

```json
{
    "data": [
        {
            "messaging_product": "whatsapp",
            "wa_id": "16505551234"
        }
    ],
    "paging": {
        "cursors": {
            "before": "eyJvZAmZAzZAXQiOjAsInZAlcnNpb25JZACI6IjE3Mzc2Nzk2ODgzODM1ODQifQZDZD",
            "after": "eyJvZAmZAzZAXQiOjAsInZAlcnNpb25JZACI6IjE3Mzc2Nzk2ODgzODM1ODQifQZDZD"
        }
    }
}
```

---

## Block User(s)

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/block_users`

### Body Parameters

```json
{
    "messaging_product": "whatsapp",
    "block_users": [
        {
            "input": "+16505551234"
        }
    ]
}
```

### Response Example

```json
{
    "messaging_product": "whatsapp",
    "block_users": {
        "added_users": [
            {
                "input": "+16505551234",
                "wa_id": "16505551234"
            }
        ]
    }
}
```

---

## Unblock User(s)

**Endpoint:** `DELETE https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/block_users`

### Body Parameters

```json
{
    "messaging_product": "whatsapp",
    "block_users": [
        {
            "input": "+16505551234"
        }
    ]
}
```

### Response Example

```json
{
    "messaging_product": "whatsapp",
    "block_users": {
        "removed_users": [
            {
                "input": "+16505551234",
                "wa_id": "16505551234"
            }
        ]
    }
}
```
# Media Management

Upload media, manage media IDs, and generate QR codes for your WhatsApp Business Account.

## Using the Service

### Upload Media
```php
use Shakil\Fast2sms\Facades\Fast2sms;

$response = Fast2sms::whatsapp()->uploadMedia('/path/to/file.jpg', 'image/jpeg');

if ($response->isSuccess()) {
    $mediaId = $response->id;
}
```

### QR Code Management
```php
// Create QR Code
$response = Fast2sms::whatsapp()->manageQrCodes('POST', null, [
    'prefilled_message' => 'Hello',
    'generate_qr_image' => 'SVG'
]);
```

---

## API Reference (Low-level)

### Get Media URL From Media ID

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/media/{media_id}`

### Parameters

| Type | Parameter | Data Type | Required | Description |
|------|-----------|-----------|----------|-------------|
| Path | `version` | string | Yes | Defaults to `v24.0`. |
| Path | `phone_number_id` | string | Yes | The ID of the phone number. |
| Path | `media_id` | string | Yes | The ID of the media. |
| Query | `authorization` | string | Yes | Your API Key. |

### Response Example

```json
{
    "messaging_product": "whatsapp",
    "url": "<URL>",
    "mime_type": "<MIME_TYPE>",
    "sha256": "<HASH>",
    "file_size": "<FILE_SIZE>",
    "id": "<MEDIA_ID>"
}
```

---

## Create Media Handle

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/media_handle`

### Body Parameters (Multipart/form-data)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `file` | file | Yes | The media file to upload. |
| `messaging_product` | string | Yes | Defaults to `whatsapp`. |
| `type` | string | Yes | The MIME type of the media file. |

### Response Example

```json
{
    "h": "2:c2FtcGx..."
}
```

---

## Upload Media

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/media`

### Body Parameters (Multipart/form-data)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `file` | file | Yes | The media file to upload. |
| `messaging_product` | string | Yes | Defaults to `whatsapp`. |
| `type` | string | Yes | The MIME type of the media file. |

### Response Example

```json
{
    "id": "13658294123"
}
```

---

## QR Code Management

### List of QR Codes

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/message_qrdls`

### Get QR Code

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/message_qrdls/{qr_code_id}`

### Create QR Code

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/message_qrdls`

**Body:**
```json
{
    "prefilled_message": "Hello",
    "generate_qr_image": "SVG"
}
```

### Update QR Code

**Endpoint:** `POST https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/message_qrdls/{qr_code_id}`

### Delete QR Code

**Endpoint:** `DELETE https://www.fast2sms.com/dev/whatsapp/{version}/{phone_number_id}/message_qrdls/{qr_code_id}`
# WhatsApp Analytics

Retrieve logs and summaries for your WhatsApp usage.

## Using the Service

### Get WhatsApp Logs
```php
use Shakil\Fast2sms\Facades\Fast2sms;

$response = Fast2sms::whatsapp()->getLogs('2023-10-01', '2023-10-03');

if ($response->isSuccess()) {
    $logs = $response->data;
}
```

### Get WhatsApp Summary
```php
$response = Fast2sms::whatsapp()->getSummary('2023-09-01', '2023-09-30');

if ($response->isSuccess()) {
    $summary = $response->data;
    // $summary['delivered'], $summary['read'], etc.
}
```

---

## API Reference (Low-level)

### WhatsApp Logs

Fetch WhatsApp logs for the last 3 days.

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp_logs`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `authorization` | string | Yes | Your API Key. |
| `from` | date | Yes | From date in format `YYYY-MM-DD`. |
| `to` | date | Yes | To date in format `YYYY-MM-DD`. |

### Response Example

```json
{
  "success": true,
  "message": "Whatsapp Logs generated successfully.",
  "data": [
    {
      "type": "status_update",
      "request_id": "NlOPxxxxxxxxxxxx",
      "phone_number_id": "155xxxxxxxxxx",
      "recipient_id": "916xxxxxxxxx",
      "status": "delivered",
      "timestamp": "1763717022",
      "errors": null
    }
  ]
}
```

---

## WhatsApp Logs Summary

Get WhatsApp logs summary for a 30-day interval.

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp_summary`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `authorization` | string | Yes | Your API Key. |
| `from` | date | Yes | From date in format `YYYY-MM-DD`. |
| `to` | date | Yes | To date in format `YYYY-MM-DD`. |

### Response Example

```json
{
  "success": true,
  "message": "WhatsApp Summary fetched successfully.",
  "data": {
    "sent": 10,
    "accepted": 16,
    "delivered": 97,
    "read": 5,
    "failed": 3,
    "rejected": 0,
    "pending": 8
  }
}
```
# WhatsApp Delivery Reports

The package supports receiving delivery reports via webhooks or fetching them manually.

## Using the Service

### Fetch Report Manually
```php
use Shakil\Fast2sms\Facades\Fast2sms;

$response = Fast2sms::whatsapp()->getDeliveryReport('REQUEST_ID');

if ($response->isSuccess()) {
    $report = $response->data;
}
```

### Set Webhook URL
```php
Fast2sms::whatsapp()->setWebhook('https://your-site.com/webhook/whatsapp');
```

---

## Webhooks

### Set WhatsApp Webhook

You can receive real-time WhatsApp delivery reports as a JSON POST object on your provided URL.

**Panel configuration:** [Dashboard Dev API](https://www.fast2sms.com/dashboard/dev-api) -> API Webhook -> WhatsApp Webhook tab.

### Webhook Payload Example

```json
{
  "whatsapp_reports": [
    {
      "type": "incoming_message",
      "message_id": "wamid.ABCD1234",
      "phone_number_id": "123456789012345",
      "from": "919876543210",
      "timestamp": 1726642924,
      "message_type": "text",
      "body": "Hello, this is a customer response",
      "context": {
        "replied_to_message_id": "wamid.EFGH5678"
      }
    },
    {
      "type": "status_update",
      "request_id": "wamid.ABCD1234",
      "phone_number_id": "123456789012345",
      "recipient_id": "919876543210",
      "status": "delivered",
      "timestamp": 1726643040,
      "errors": null
    }
  ]
}
```

---

## Get WhatsApp Webhook URL

**Endpoint:** `GET https://www.fast2sms.com/dev/webhook/whatsapp/get`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `authorization` | string | Yes | Your API Key. |

### Response Example

```json
{
  "return": "true",
  "data": [
    {
      "webhook_url": "https://your-site.com/webhook",
      "webhook_status": "enable"
    }
  ]
}
```

---

## Set WhatsApp Webhook URL

**Endpoint:** `POST https://www.fast2sms.com/dev/webhook/whatsapp/set`

### Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `webhook_url` | string | No | The URL to receive webhooks. |
| `webhook_status` | string | No | `enable` or `disable`. |

### Response Example

```json
{
  "message": "Webhook settings updated successfully"
}
```

---

## Fetch Reports Manually

**Endpoint:** `GET https://www.fast2sms.com/dev/whatsapp/{REQUEST_ID}`

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `REQUEST_ID` | string | Yes | The WhatsApp request ID. |

### Response Example

```json
{
    "success": true,
    "message": "DLR found successfully.",
    "data": [
        {
            "type": "status_update",
            "request_id": "Ee03991ro9bLN8m",
            "phone_number_id": "15557141824",
            "recipient_id": 919737304031,
            "status": "accepted",
            "timestamp": null,
            "errors": null
        }
    ]
}
```
