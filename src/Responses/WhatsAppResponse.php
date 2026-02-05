<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use InvalidArgumentException;

use function is_bool;

/**
 * A class to handle responses from the Fast2sms WhatsApp API.
 */
class WhatsAppResponse extends Fast2smsResponse
{
    /**
     * @param array<string, mixed> $data The raw response data from the API.
     *
     * @throws InvalidArgumentException if the response data is invalid or malformed.
     */
    public function __construct(protected array $data)
    {
        if ($this->data === []) {
            throw new InvalidArgumentException('Response data cannot be empty.');
        }

        // WhatsApp APIs might use 'success' or 'status' or 'return'
        $this->success = $this->data['success'] ?? $this->data['status'] ?? $this->data['return'] ?? false;

        if (! is_bool($this->success)) {
            $this->success = (bool) $this->success;
        }

        $this->message = $this->extractWhatsAppMessage();
    }

    /**
     * Extracts a human-readable message from the response data.
     */
    private function extractWhatsAppMessage(): string
    {
        if (isset($this->data['message']) && is_string($this->data['message'])) {
            return $this->data['message'];
        }

        return $this->data['error']['message'] ?? 'No message provided';
    }
}
