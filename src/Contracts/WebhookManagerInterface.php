<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

/**
 * Contract for accessing the inbound delivery-status webhook handler.
 */
interface WebhookManagerInterface
{
    /**
     * Access the delivery-status webhook handler.
     */
    public function webhook(): WebhookHandlerInterface;
}
