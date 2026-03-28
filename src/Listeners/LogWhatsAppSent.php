<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Listeners;

use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Models\Fast2smsLog;

class LogWhatsAppSent
{
    public function handle(WhatsAppSent $event): void
    {
        if (! config('fast2sms.database_logging')) {
            return;
        }

        Fast2smsLog::create([
            'payload' => $event->payload,
            'response' => $event->response->getRawData(),
            'is_success' => true,
        ]);
    }
}
