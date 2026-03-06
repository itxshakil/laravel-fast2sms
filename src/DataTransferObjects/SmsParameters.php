<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\DataTransferObjects;

use Shakil\Fast2sms\Contracts\SmsSenderInterface;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;

/**
 * Data Transfer Object for SMS parameters.
 *
 * @immutable
 */
readonly class SmsParameters
{
    /**
     * @param array<int|string>              $numbers
     * @param array<int, string>|string|null $variablesValues
     */
    public function __construct(
        public array $numbers,
        public ?string $message,
        public SmsRoute $route,
        public ?SmsLanguage $language = null,
        public ?string $senderId = null,
        public ?string $entityId = null,
        public ?string $templateId = null,
        public array|string|null $variablesValues = null,
        public bool $flash = false,
        public ?string $scheduleTime = null,
    ) {}

    /**
     * Create a new instance from a SmsSenderInterface object.
     */
    public static function fromFast2sms(SmsSenderInterface $fast2sms): self
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
