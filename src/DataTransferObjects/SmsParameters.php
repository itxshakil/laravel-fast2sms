<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\DataTransferObjects;

use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Fast2sms;

/**
 * Data Transfer Object for SMS parameters.
 *
 * This class encapsulates all parameters required for sending an SMS through the Fast2SMS service.
 * It provides an immutable structure for SMS configuration and supports both regular and scheduled messages.
 *
 * @immutable
 */
readonly class SmsParameters
{
    /**
     * Creates a new SMS parameters instance.
     *
     * @param  array<int|string>  $numbers  List of phone numbers to send SMS to
     * @param  string  $message  The SMS message content
     * @param  SmsRoute  $route  The routing method for the SMS
     * @param  SmsLanguage|null  $language  The language encoding for the message (default: null)
     * @param  string|null  $senderId  The sender ID for the message (3-6 characters)
     * @param  string|null  $entityId  The DLT entity ID for the message
     * @param  string|null  $templateId  The DLT template ID for the message
     * @param  array|string|null  $variablesValues  Template variables values
     * @param  bool  $flash  Whether to send as a flash message
     * @param  string|null  $scheduleTime  Scheduled time for delayed sending (ISO 8601 format)
     */
    public function __construct(
        public array $numbers,
        public string $message,
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
     * Creates an SmsParameters instance from a Fast2sms object.
     *
     * This factory method extracts all necessary parameters from an existing Fast2sms instance
     * and creates a new SmsParameters object with those values.
     *
     * @param  Fast2sms  $fast2sms  The source Fast2sms instance
     * @return self New SmsParameters instance with values from Fast2sms object
     */
    public static function fromFast2sms(Fast2sms $fast2sms): self
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
