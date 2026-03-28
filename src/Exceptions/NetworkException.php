<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

use Throwable;

/**
 * Thrown when a network-level failure occurs (connection timeout, DNS failure, etc.).
 */
class NetworkException extends Fast2smsException
{
    public function __construct(
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
