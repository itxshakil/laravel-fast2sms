<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

interface ResponseInterface
{
    /**
     * Determine if the API call was successful.
     */
    public function isSuccess(): bool;

    /**
     * Get the human-readable message from the response.
     */
    public function getMessage(): ?string;

    /**
     * Get the raw response data.
     *
     * @return array<string, mixed>
     */
    public function getRawData(): array;

    /**
     * Get the raw response data as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
