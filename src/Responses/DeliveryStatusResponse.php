<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use function is_scalar;

use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Enums\DeliveryStatus;

/**
 * Typed value object wrapping a Fast2sms delivery-status (DLR) webhook payload.
 *
 * Unlike API responses, an inbound webhook payload has no `return`/`success`
 * key, so this response implements {@see ResponseInterface} directly instead of
 * extending {@see Fast2smsResponse}. "Success" here means the message was
 * delivered.
 */
final readonly class DeliveryStatusResponse implements ResponseInterface
{
    /**
     * @param array<string, mixed> $data The raw webhook payload.
     */
    public function __construct(
        public DeliveryStatus $status,
        public ?string $requestId,
        public ?string $mobile,
        public ?string $channel,
        public ?string $failureReason,
        public ?string $amountDebited,
        public ?int $deliveryTimestamp,
        public int $postAttempt,
        private array $data,
    ) {}

    /**
     * Build a DeliveryStatusResponse from a raw webhook payload.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            status: DeliveryStatus::fromRaw(self::string($payload, 'status')),
            requestId: self::string($payload, 'request_id'),
            mobile: self::string($payload, 'mobile') ?? self::string($payload, 'recipient_id'),
            channel: self::string($payload, 'channel'),
            failureReason: self::string($payload, 'failure_reason'),
            amountDebited: self::string($payload, 'amount_debited'),
            deliveryTimestamp: self::int($payload, 'delivery_timestamp'),
            postAttempt: self::int($payload, 'post_attempt') ?? 1,
            data: $payload,
        );
    }

    public function isSuccess(): bool
    {
        return $this->status->isDelivered();
    }

    public function getMessage(): ?string
    {
        return self::string($this->data, 'status_description')
            ?? self::string($this->data, 'description')
            ?? $this->failureReason;
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
     * @param array<string, mixed> $payload
     */
    private static function string(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_scalar($value) && $value !== '' ? (string) $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function int(array $payload, string $key): ?int
    {
        $value = $payload[$key] ?? null;

        return is_scalar($value) && $value !== '' ? (int) $value : null;
    }
}
