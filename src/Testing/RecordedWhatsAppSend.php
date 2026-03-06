<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Testing;

use DateTimeImmutable;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;

/**
 * Represents a single recorded WhatsApp send captured during faking.
 */
readonly class RecordedWhatsAppSend
{
    public function __construct(
        public WhatsAppParameters $parameters,
        public DateTimeImmutable $sentAt,
    ) {}
}
