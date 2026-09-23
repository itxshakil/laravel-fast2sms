<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Webhooks;

use Illuminate\Http\Request;

use function is_array;

use Shakil\Fast2sms\Contracts\WebhookHandlerInterface;
use Shakil\Fast2sms\Enums\DeliveryStatus;
use Shakil\Fast2sms\Events\MessageDelivered;
use Shakil\Fast2sms\Events\MessageFailed;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

/**
 * Default handler for inbound Fast2sms delivery-status (DLR) webhooks.
 *
 * Supports the Standard (custom) payload as well as the CleverTap (SMS)
 * template, which nests one or more statuses under a `statuses` array.
 */
class WebhookHandler implements WebhookHandlerInterface
{
    public function handle(Request $request): DeliveryStatusResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->all();

        $response = $this->parse($payload);

        $this->dispatch($response);

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function parse(array $payload): DeliveryStatusResponse
    {
        return DeliveryStatusResponse::fromPayload(
            $this->normalise($payload),
        );
    }

    /**
     * Flatten the CleverTap `statuses` envelope onto the Standard payload shape.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalise(array $payload): array
    {
        $statuses = $payload['statuses'] ?? null;

        if (is_array($statuses) && isset($statuses[0]) && is_array($statuses[0])) {
            $first = $statuses[0];

            return [
                'request_id' => $first['msgId'] ?? null,
                'status' => $first['status'] ?? null,
                'status_description' => $first['description'] ?? null,
                'delivery_timestamp' => $first['timestamp'] ?? null,
            ] + $payload;
        }

        return $payload;
    }

    private function dispatch(DeliveryStatusResponse $response): void
    {
        if (! config('fast2sms.events.enabled', true)) {
            return;
        }

        match ($response->status) {
            DeliveryStatus::Delivered => event(new MessageDelivered($response)),
            DeliveryStatus::Failed => event(new MessageFailed($response)),
            DeliveryStatus::Pending => null,
        };
    }
}
