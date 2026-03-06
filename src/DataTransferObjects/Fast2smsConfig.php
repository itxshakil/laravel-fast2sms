<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\DataTransferObjects;

use Shakil\Fast2sms\Enums\SmsRoute;

/**
 * Typed value object wrapping the fast2sms configuration array.
 *
 * Replaces raw `array $config` usage throughout the package, providing
 * IDE autocompletion, type-safety, and a single source of truth for defaults.
 */
final readonly class Fast2smsConfig
{
    public function __construct(
        public string $apiKey,
        public string $baseUrl,
        public string $driver,
        public int $timeout,
        public SmsRoute $defaultRoute,
        public ?string $defaultSenderId,
        public string $whatsappPhoneNumberId = '',
        public string $whatsappWabaId = '',
        public string $whatsappVersion = 'v24.0',
    ) {}

    /**
     * Construct a Fast2smsConfig from a raw configuration array.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            apiKey: $config['api_key'] ?? '',
            baseUrl: $config['base_url'] ?? '',
            driver: $config['driver'] ?? 'api',
            timeout: (int) ($config['timeout'] ?? 30),
            defaultRoute: SmsRoute::from($config['default_route'] ?? SmsRoute::QUICK->value),
            defaultSenderId: $config['default_sender_id'] ?? null,
            whatsappPhoneNumberId: (string) ($config['whatsapp']['default_phone_number_id'] ?? ''),
            whatsappWabaId: (string) ($config['whatsapp']['default_waba_id'] ?? ''),
            whatsappVersion: (string) ($config['whatsapp']['version'] ?? 'v24.0'),
        );
    }
}
