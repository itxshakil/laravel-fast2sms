<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Shakil\Fast2sms\Http\Controllers\WebhookController;
use Shakil\Fast2sms\Http\Middleware\VerifyFast2smsWebhook;

/*
|--------------------------------------------------------------------------
| Fast2sms Webhook Route
|--------------------------------------------------------------------------
|
| Registered only when `fast2sms.webhook.enabled` and `webhook.auto_route`
| are both true. The trailing {secret} segment is verified in constant time
| by the VerifyFast2smsWebhook middleware. Point your Fast2sms dashboard
| webhook URL at:  https://your-app.test/{path}/{secret}
|
*/

$path = mb_trim((string) config('fast2sms.webhook.path', 'fast2sms/webhook'), '/');

/** @var array<int, string> $middleware */
$middleware = (array) config('fast2sms.webhook.middleware', ['api']);
$middleware[] = VerifyFast2smsWebhook::class;

Route::middleware($middleware)
    ->post($path . '/{secret?}', WebhookController::class)
    ->name('fast2sms.webhook');
