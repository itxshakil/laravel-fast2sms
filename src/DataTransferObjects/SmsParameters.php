<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\DataTransferObjects;

use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;

class SmsParameters
{
    public function __construct(
        public readonly array $numbers,
        public readonly string $message,
        public readonly SmsRoute $route,
        public readonly ?SmsLanguage $language = null,
        public readonly ?string $senderId = null,
        public readonly ?string $entityId = null,
        public readonly ?string $templateId = null,
        public readonly array|string|null $variablesValues = null,
        public readonly bool $flash = false,
        public readonly ?string $scheduleTime = null,
    ) {}

    public static function fromFast2sms(\Shakil\Fast2sms\Fast2sms $fast2sms): self
    {
        return new self(
            numbers: $fast2sms->getNumbers(),
            message: $fast2sms->getMessage(),
            route: $fast2sms->getRoute(),
            language: $fast2sms->getLanguage(),
            senderId: $fast2sms->getSenderId(),
            entityId: $fast2sms->getEntityId(),
            templateId: $fast2sms->getTemplateId(),
            variablesValues: $fast2sms->getVariablesValues(),
            flash: $fast2sms->isFlash(),
            scheduleTime: $fast2sms->getScheduleTime(),
        );
    }
}
