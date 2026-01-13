<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Listeners;

use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Models\Fast2smsLog;

class LogSmsFailed
{
    public function handle(SmsFailed $event): void
    {
        if (! config('fast2sms.database_logging')) {
            return;
        }

        Fast2smsLog::create([
            'payload' => $event->payload,
            'response' => $event->response,
            'is_success' => false,
            'error_message' => $event->exception->getMessage(),
        ]);
    }
}
