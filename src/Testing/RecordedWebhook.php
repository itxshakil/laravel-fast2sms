<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Testing;

use DateTimeImmutable;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

/**
 * Represents a single inbound delivery-status webhook handled during faking.
 */
readonly class RecordedWebhook
{
    public function __construct(
        public DeliveryStatusResponse $response,
        public DateTimeImmutable $receivedAt,
    ) {}
}
