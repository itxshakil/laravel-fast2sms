<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Event fired when an SMS send fails via Fast2sms.
 */
class SmsFailed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param array      $payload   The data payload attempted to be sent to Fast2sms.
     * @param Throwable  $exception The exception that occurred.
     * @param array|null $response  The API response, if any was received before the error.
     */
    public function __construct(
        public array $payload,
        public Throwable $exception,
        public ?array $response = null,
    ) {}
}
