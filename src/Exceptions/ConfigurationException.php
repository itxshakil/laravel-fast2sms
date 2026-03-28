<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

/**
 * Thrown when the Fast2sms package configuration is missing or invalid.
 */
class ConfigurationException extends Fast2smsException
{
    public static function queuingNotEnabled(): self
    {
        return new self('Queuing is not enabled. Set fast2sms.queue.enabled = true in your config before calling onQueue() or onConnection().');
    }
}
