<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Enums\ResponseType;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppSent;

class ResponseFactory
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $data
     */
    public static function make(
        array $payload,
        array $data,
        ResponseType $type = ResponseType::Generic,
    ): ResponseInterface {
        return match ($type) {
            ResponseType::WalletBalance => new WalletBalanceResponse($data),
            ResponseType::Sms => self::makeSmsResponse($payload, $data),
            ResponseType::DltManager => new DltManagerResponse($data),
            ResponseType::WhatsApp => self::makeWhatsAppResponse($payload, $data),
            ResponseType::Generic => self::fallbackDetect($payload, $data),
        };
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $data
     */
    private static function makeSmsResponse(array $payload, array $data): SmsResponse
    {
        $smsResponse = new SmsResponse($data);

        if (config('fast2sms.events.enabled', true)) {
            event(new SmsSent($payload, $smsResponse));
        }

        return $smsResponse;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $data
     */
    private static function makeWhatsAppResponse(array $payload, array $data): WhatsAppResponse
    {
        $response = new WhatsAppResponse($data);

        if (config('fast2sms.events.enabled', true)) {
            event(new WhatsAppSent($payload, $response));
        }

        return $response;
    }

    /**
     * Heuristic fallback used when the caller does not supply an explicit ResponseType.
     *
     * Detection relies on the presence of well-known keys in the API response:
     *   - `wallet`      → WalletBalanceResponse
     *   - `request_id`  → SmsResponse
     *   - `success`+`data` → DltManagerResponse
     *   - anything else → Fast2smsResponse (generic)
     *
     * WARNING: This detection is fragile. If the Fast2sms API changes its response
     * shape (e.g. renames or removes these keys), the wrong response class will be
     * instantiated silently. Prefer passing an explicit ResponseType at every call
     * site that knows the expected response type to avoid relying on this fallback.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $data
     */
    private static function fallbackDetect(array $payload, array $data): ResponseInterface
    {
        if (isset($data['wallet'])) {
            return new WalletBalanceResponse($data);
        }

        if (isset($data['request_id'])) {
            return self::makeSmsResponse($payload, $data);
        }

        if (isset($data['success'], $data['data'])) {
            return new DltManagerResponse($data);
        }

        return new Fast2smsResponse($data);
    }
}
