<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

use Throwable;

/**
 * Thrown when the Fast2sms API returns an error response.
 */
class ApiException extends Fast2smsException
{
    /**
     * @param array<string, mixed> $responseBody The raw API response body.
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $responseBody = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Create an ApiException mapped to the correct subtype based on HTTP status code.
     *
     * @param array<string, mixed> $responseBody
     */
    public static function fromStatus(int $status, string $message, array $responseBody = [], ?Throwable $previous = null): self
    {
        return match (true) {
            $status === 401 => new AuthenticationException($message, $responseBody, $previous),
            $status === 429 => new RateLimitException($message, $responseBody, $previous),
            $status >= 500 => new ServerException($message, $status, $responseBody, $previous),
            default => new self($message, $status, $responseBody, $previous),
        };
    }
}
