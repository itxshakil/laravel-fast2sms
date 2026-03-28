<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

/**
 * Thrown when a duplicate send is detected within the deduplication TTL window.
 */
class DuplicateSendException extends Fast2smsException
{
    public static function detected(string $hash): self
    {
        return new self("Duplicate send detected (hash: $hash). This message was already sent within the deduplication window.");
    }
}
