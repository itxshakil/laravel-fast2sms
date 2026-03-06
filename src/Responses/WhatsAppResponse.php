<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use InvalidArgumentException;

use function is_bool;
use function is_string;

class WhatsAppResponse extends Fast2smsResponse
{
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

        $this->success = $this->data['success'] ?? $this->data['status'] ?? $this->data['return'] ?? false;

        if (! is_bool($this->success)) {
            $this->success = (bool) $this->success;
        }

        $this->message = $this->extractWhatsAppMessage();
    }

    private function extractWhatsAppMessage(): string
    {
        if (isset($this->data['message']) && is_string($this->data['message'])) {
            return $this->data['message'];
        }

        return $this->data['error']['message'] ?? 'No message provided';
    }
}
