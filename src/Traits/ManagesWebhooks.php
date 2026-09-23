<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Shakil\Fast2sms\Contracts\WebhookHandlerInterface;

/**
 * Exposes the inbound delivery-status webhook handler on the Fast2sms service.
 */
trait ManagesWebhooks
{
    /**
     * Access the delivery-status webhook handler.
     *
     * Use this to process an inbound webhook from a custom route:
     * `Fast2sms::webhook()->handle($request)`.
     */
    public function webhook(): WebhookHandlerInterface
    {
        return app(WebhookHandlerInterface::class);
    }
}
