<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Listeners;

use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Models\Fast2smsLog;

class LogSmsSent
{
    public function handle(SmsSent $event): void
    {
        if (! config('fast2sms.database_logging')) {
            return;
        }

        Fast2smsLog::create([
            'request_id' => $event->response->requestId,
            'payload' => $event->payload,
            'response' => $event->response->json(),
            'is_success' => true,
        ]);
    }
}
