<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\DataTransferObjects;

use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Enums\WhatsAppType;

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
     * @param array<string, mixed>|null             $location
     * @param array<string, mixed>|null             $interactive
     */
    public function __construct(
        public string|array $to,
        public ?string $phoneNumberId = null,
        public ?WhatsAppType $type = null,
        public ?string $body = null,
        public ?string $templateId = null,
        public ?array $variables = null,
        public ?string $mediaUrl = null,
        public ?string $documentFilename = null,
        public ?string $messageId = null,
        public ?string $emoji = null,
        public ?array $components = null,
        public ?array $location = null,
        public ?array $interactive = null,
    ) {}

    /**
     * Create a new instance from a WhatsApp object.
     */
    public static function fromWhatsApp(WhatsAppInterface $whatsapp): self
    {
        return new self(
            to: $whatsapp->getTo(),
            phoneNumberId: $whatsapp->getFromPhoneNumberId(),
            type: $whatsapp->getType(),
            body: $whatsapp->getBody(),
            templateId: $whatsapp->getTemplateId(),
            variables: $whatsapp->getVariables(),
            mediaUrl: $whatsapp->getMediaUrl(),
            documentFilename: $whatsapp->getDocumentFilename(),
            messageId: $whatsapp->getMessageId(),
            emoji: $whatsapp->getEmoji(),
            components: $whatsapp->getComponents(),
            location: $whatsapp->getLocation(),
            interactive: $whatsapp->getInteractive(),
        );
    }
}
