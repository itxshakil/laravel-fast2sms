<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

use Throwable;

/**
 * Thrown when the API returns a 429 Too Many Requests response.
 */
class RateLimitException extends ApiException
{
    /**
     * @param array<string, mixed> $responseBody
     */
    public function __construct(
        string $message,
        array $responseBody = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $responseBody, $previous);
    }
}
