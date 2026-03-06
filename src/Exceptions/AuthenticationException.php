<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

use Throwable;

/**
 * Thrown when the API returns a 401 Unauthorized response.
 */
class AuthenticationException extends ApiException
{
    /**
     * @param array<string, mixed> $responseBody
     */
    public function __construct(
        string $message,
        array $responseBody = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 401, $responseBody, $previous);
    }
}
