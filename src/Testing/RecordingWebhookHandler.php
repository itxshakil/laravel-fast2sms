<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Testing;

use Illuminate\Http\Request;
use Shakil\Fast2sms\Contracts\WebhookHandlerInterface;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

/**
 * Decorates the real webhook handler so every handled delivery-status
 * webhook is recorded on the active Fast2smsFake.
 *
 * Behaviour is unchanged: events are still dispatched and logs are still
 * reconciled, so listeners and Event::fake() work exactly as in production.
 */
final class RecordingWebhookHandler implements WebhookHandlerInterface
{
    public function __construct(
        private readonly WebhookHandlerInterface $inner,
        private readonly Fast2smsFake $fake,
    ) {}

    public function handle(Request $request): DeliveryStatusResponse
    {
        $response = $this->inner->handle($request);

        $this->fake->recordWebhook($response);

        return $response;
    }

    public function parse(array $payload): DeliveryStatusResponse
    {
        return $this->inner->parse($payload);
    }
}
