<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

use Exception;
use Throwable;

/**
 * Custom exception for Fast2sms package errors.
 */
class Fast2smsException extends Exception
{
    /**
     * Create a new Fast2smsException instance.
     *
     * @param  string  $message  The exception message.
     * @param  int  $code  The exception code.
     * @param  Throwable|null  $previous  The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
