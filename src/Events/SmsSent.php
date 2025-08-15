<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shakil\Fast2sms\Responses\SmsResponse;

/**
 * Event fired when an SMS is successfully sent via Fast2sms.
 */
class SmsSent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array  $payload  The data payload sent to Fast2sms.
     * @param  SmsResponse  $response  The successful response from Fast2sms API.
     */
    public function __construct(
        public array $payload,
        public SmsResponse $response
    ) {}
}
