<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use function count;

use InvalidArgumentException;

use function is_array;
use function is_bool;
use function is_int;
use function is_string;

/**
 * A class to handle responses from the Fast2sms API.
 *
 * This class provides a structured way to access data from the API response,
 * including checking for success, retrieving error codes and messages, and
 * accessing the raw response data.
 */
class Fast2smsResponse
{
    /**
     * Indicates whether the API call was successful.
     */
    public bool $success;

    /**
     * The message returned by the API, if any.
     */
    public ?string $message;

    /**
     * The raw data from the Fast2sms API response.
     *
     * @param array<string, mixed> $data The raw response data from the API.
     *
     * @throws InvalidArgumentException if the response data is invalid or malformed.
     */
    public function __construct(protected array $data)
    {
        if ($this->data === []) {
            throw new InvalidArgumentException('Response data cannot be empty.');
        }

        if (! is_array($this->data)) {
            throw new InvalidArgumentException('Response data must be an array.');
        }

        if (! isset($this->data['return']) && ! isset($this->data['success'])) {
            throw new InvalidArgumentException('Response data must contain "return" or "success" key.');
        }

        if (! is_bool($this->data['return'] ?? $this->data['success'])) {
            throw new InvalidArgumentException('"return" or "success" key must be a boolean.');
        }

        if (isset($this->data['status_code']) && ! is_int($this->data['status_code'])) {
            throw new InvalidArgumentException('"status_code" key must be an integer.');
        }

        $this->success = $this->data['return'] ?? $this->data['success'];
        $this->message = $this->message();
    }

    /**
     * Gets the error message from the response.
     *
     * @return string|null The error message if available, otherwise null.
     */
    public function getErrorMessage(): ?string
    {
        return is_string($this->data['message'] ?? null)
            ? $this->data['message']
            : null;
    }

    /**
     * Determines if the API call was successful.
     *
     * @return bool True if the API call was successful, otherwise false.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Gets the error code from the response.
     *
     * @return int|null The error code if available, otherwise null.
     */
    public function getErrorCode(): ?int
    {
        return $this->data['status_code'] ?? null;
    }

    /**
     * Gets the raw response data.
     *
     * @return array The complete, raw response data from the API.
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Gets the raw response data as an array.
     *
     * This is an alias for getRawData().
     *
     * @return array The complete, raw response data from the API.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Extracts a human-readable message from the response data.
     *
     * This method handles various formats of the 'message' key in the response
     * and falls back to a default message if no message is found.
     *
     * @return string The extracted message or a default message if none is found.
     */
    private function message(): string
    {
        if (isset($this->data['message']) && is_string($this->data['message'])) {
            return $this->data['message'];
        }

        if (isset($this->data['message']) && is_array($this->data['message']) && count($this->data['message']) > 0) {
            return $this->data['message'][array_key_first($this->data['message'])];
        }

        $errorMessage = $this->getErrorMessage();
        if ($errorMessage) {
            return $errorMessage;
        }

        return 'No message provided';
    }
}
