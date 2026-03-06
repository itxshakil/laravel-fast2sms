<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Event fired when a WhatsApp message is successfully sent via Fast2sms.
 */
class WhatsAppSent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param array<string, mixed> $payload  The data payload sent to Fast2sms.
     * @param WhatsAppResponse     $response The successful response from Fast2sms API.
     */
    public function __construct(
        public array $payload,
        public WhatsAppResponse $response,
    ) {}
}
