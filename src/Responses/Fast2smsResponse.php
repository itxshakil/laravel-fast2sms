<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use function count;

use InvalidArgumentException;

use function is_array;
use function is_bool;
use function is_int;
use function is_string;

use Shakil\Fast2sms\Contracts\ResponseInterface;

/**
 * Base response wrapper for Fast2sms API responses.
 */
class Fast2smsResponse implements ResponseInterface
{
    public bool $success;

    public ?string $message;

    /**
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException
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

    public function __get(string $name): mixed
    {
        if ($name === 'requestId') {
            return $this->data['request_id'] ?? null;
        }

        return $this->data[$name] ?? null;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getErrorMessage(): ?string
    {
        return is_string($this->data['message'] ?? null)
            ? $this->data['message']
            : null;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorCode(): ?int
    {
        return $this->data['status_code'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        return $this->data;
    }

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
