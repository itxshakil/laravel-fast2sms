<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use function is_array;

/**
 * @property-read string|null $requestId
 */
class SmsResponse extends Fast2smsResponse
{
    public function getRequestId(): ?string
    {
        return $this->data['request_id'] ?? null;
    }

    /**
     * @return array<int, mixed>
     */
    public function getMessages(): array
    {
        return is_array($this->data['message'] ?? null)
            ? $this->data['message']
            : [];
    }
}
