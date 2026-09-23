<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use Illuminate\Http\Request;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

/**
 * Contract for processing inbound Fast2sms delivery-status (DLR) webhooks.
 */
interface WebhookHandlerInterface
{
    /**
     * Process an inbound webhook request: parse it, dispatch the matching
     * delivery event, and reconcile the log (when enabled).
     */
    public function handle(Request $request): DeliveryStatusResponse;

    /**
     * Parse a raw webhook payload into a typed response object without
     * dispatching events or touching the database.
     *
     * @param array<string, mixed> $payload
     */
    public function parse(array $payload): DeliveryStatusResponse;
}
