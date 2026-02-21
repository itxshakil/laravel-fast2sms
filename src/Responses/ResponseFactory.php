<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Exceptions\Fast2smsException;

/**
 * Factory class to create the appropriate Fast2smsResponse object based on the API response data.
 *
 * This factory centralizes the logic for mapping raw API responses to their specific
 * response classes (e.g., SmsResponse, WalletBalanceResponse, DltManagerResponse).
 */
class ResponseFactory
{
    /**
     * Create a Fast2smsResponse instance from the given data.
     *
     * @param array<string, mixed> $payload The original request payload.
     * @param array<string, mixed> $data    The raw response data from the API.
     *
     * @return Fast2smsResponse The specific response object (e.g., SmsResponse, WalletBalanceResponse, DltManagerResponse).
     */
    public static function make(array $payload, array $data): Fast2smsResponse
    {
        if (isset($data['wallet'])) {
            return new WalletBalanceResponse($data);
        }

        if (isset($data['request_id'])) {
            $smsResponse = new SmsResponse($data);
            event(new SmsSent($payload, $smsResponse));

            return $smsResponse;
        }

        if (isset($data['success'], $data['data'])) {
            return new DltManagerResponse($data);
        }

        return new Fast2smsResponse($data);
    }
}
