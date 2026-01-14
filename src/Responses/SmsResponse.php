<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use function is_array;

/**
 * A class to handle general SMS sending responses from the Fast2sms API.
 *
 * This class extends the base Fast2smsResponse and provides specific methods
 * for accessing SMS-related data, such as the request ID and the list of messages.
 *
 * @property-read string|null $requestId
 */
class SmsResponse extends Fast2smsResponse
{
    /**
     * Gets the request ID from the response.
     *
     * The request ID is a unique identifier for the SMS sending request.
     *
     * @return string|null The request ID if available, otherwise null.
     */
    public function getRequestId(): ?string
    {
        return $this->data['request_id'] ?? null;
    }

    /**
     * Gets the messages array from the response.
     *
     * This method retrieves an array of message details, which may include
     * status, mobile number, and other information for each message sent.
     *
     * @return array<int, mixed> An array of message data, or an empty array if not present.
     */
    public function getMessages(): array
    {
        return is_array($this->data['message'] ?? null)
            ? $this->data['message']
            : [];
    }
}
