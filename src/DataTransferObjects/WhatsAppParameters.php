<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\DataTransferObjects;

/**
 * Data Transfer Object for WhatsApp parameters.
 *
 * @immutable
 */
readonly class WhatsAppParameters
{
    /**
     * @param string|array<int, string>             $to
     * @param array<int, string>|null               $variables
     * @param array<int, array<string, mixed>>|null $components
     */
    public function __construct(
        public string|array $to,
        public ?string $phoneNumberId = null,
        public ?string $type = null,
        public ?string $body = null,
        public ?string $templateId = null,
        public ?array $variables = null,
        public ?string $mediaUrl = null,
        public ?string $documentFilename = null,
        public ?array $components = null,
    ) {}
}
