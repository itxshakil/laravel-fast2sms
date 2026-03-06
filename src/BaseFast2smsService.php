<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Shakil\Fast2sms\Contracts\ClientInterface;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\DataTransferObjects\Fast2smsConfig;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Traits\HandlesFaking;

/**
 * Base class for Fast2sms service, handling core functionality.
 */
abstract class BaseFast2smsService
{
    use HandlesFaking;

    /**
     * @param ClientInterface $client The API client.
     * @param Fast2smsConfig  $config The typed package configuration.
     */
    public function __construct(
        protected ClientInterface $client,
        protected Fast2smsConfig $config,
    ) {}

    /**
     * Return a map of all package events to their descriptions.
     *
     * @return array<class-string, string>
     */
    public static function events(): array
    {
        return [
            SmsSent::class => 'Fired after a successful SMS send.',
            SmsFailed::class => 'Fired when an SMS send fails.',
            WhatsAppSent::class => 'Fired after a successful WhatsApp send.',
            WhatsAppFailed::class => 'Fired when a WhatsApp send fails.',
            LowBalanceDetected::class => 'Fired when wallet balance drops below the configured threshold.',
        ];
    }

    /**
     * Executes the API call to Fast2sms and returns the mapped response.
     *
     * @param array<string, mixed> $payload The request payload.
     * @param string               $path    The API endpoint path (default: /bulkV2).
     */
    protected function executeApiCall(array $payload = [], string $path = '/bulkV2'): ResponseInterface
    {
        return $this->client->post($path, $payload);
    }

    /**
     * Hook method executed after every API call.
     */
    protected function afterApiCall(): void {}
}
