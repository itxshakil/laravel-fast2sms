<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shakil\Fast2sms\Contracts\WebhookHandlerInterface;

/**
 * Receives Fast2sms delivery-status (DLR) webhooks.
 *
 * Authenticity is enforced upstream by the VerifyFast2smsWebhook middleware;
 * this controller only parses the payload and hands it to the handler.
 */
class WebhookController
{
    public function __construct(
        private readonly WebhookHandlerInterface $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $response = $this->handler->handle($request);

        return new JsonResponse([
            'success' => true,
            'status' => $response->status->value,
            'request_id' => $response->requestId,
        ]);
    }
}
