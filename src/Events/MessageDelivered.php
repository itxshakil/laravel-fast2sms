<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

/**
 * Event fired when a Fast2sms delivery-status webhook reports a delivered message.
 */
class MessageDelivered
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param DeliveryStatusResponse $response The parsed delivery-status payload.
     */
    public function __construct(
        public DeliveryStatusResponse $response,
    ) {}
}
