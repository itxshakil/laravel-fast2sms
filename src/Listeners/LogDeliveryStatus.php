<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Listeners;

use Shakil\Fast2sms\Events\MessageDelivered;
use Shakil\Fast2sms\Events\MessageFailed;
use Shakil\Fast2sms\Models\Fast2smsLog;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

/**
 * Reconciles a Fast2smsLog row with the delivery state reported by a webhook.
 *
 * Matches the original send by request_id and applies the terminal status,
 * failure reason and debited amount. Idempotent: a stale or duplicate webhook
 * (lower post_attempt or older delivery timestamp) is ignored.
 */
class LogDeliveryStatus
{
    public function handle(MessageDelivered|MessageFailed $event): void
    {
        if (! config('fast2sms.database_logging') || ! config('fast2sms.webhook.update_logs', true)) {
            return;
        }

        $response = $event->response;

        if ($response->requestId === null) {
            return;
        }

        $log = Fast2smsLog::query()
            ->where('request_id', $response->requestId)
            ->first();

        if ($log === null) {
            Fast2smsLog::query()->create($this->attributes($response));

            return;
        }

        if ($this->isStale($log, $response)) {
            return;
        }

        $log->fill($this->attributes($response))->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(DeliveryStatusResponse $response): array
    {
        return [
            'request_id' => $response->requestId,
            'status' => $response->status->value,
            'is_success' => ! $response->status->isFailed(),
            'failure_reason' => $response->status->isFailed() ? $response->getMessage() : null,
            'amount_debited' => $response->amountDebited,
            'delivery_timestamp' => $response->deliveryTimestamp,
            'post_attempt' => $response->postAttempt,
        ];
    }

    /**
     * Determine whether an incoming webhook is older than what is already stored.
     */
    private function isStale(Fast2smsLog $log, DeliveryStatusResponse $response): bool
    {
        $storedAttempt = $log->getAttribute('post_attempt');
        if ($storedAttempt !== null && $response->postAttempt < (int) $storedAttempt) {
            return true;
        }

        $storedTimestamp = $log->getAttribute('delivery_timestamp');

        return $storedTimestamp !== null
            && $response->deliveryTimestamp !== null
            && $response->deliveryTimestamp < (int) $storedTimestamp;
    }
}
