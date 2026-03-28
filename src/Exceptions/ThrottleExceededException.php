<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

/**
 * Thrown when the per-minute send rate limit is exceeded.
 */
class ThrottleExceededException extends Fast2smsException
{
    public static function limitReached(int $count, int $max): self
    {
        return new self("Send rate limit exceeded: $count sends in the current minute (max: $max).");
    }
}
