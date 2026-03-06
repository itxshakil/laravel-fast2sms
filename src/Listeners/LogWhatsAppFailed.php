<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Listeners;

use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Models\Fast2smsLog;

class LogWhatsAppFailed
{
    public function handle(WhatsAppFailed $event): void
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
