<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

/**
 * Thrown when input validation fails before an HTTP call is made.
 */
class ValidationException extends Fast2smsException
{
    public static function emptyMessage(): self
    {
        return new self('SMS message content cannot be empty.');
    }

    public static function missingRecipient(): self
    {
        return new self('At least one recipient number is required.');
    }

    public static function missingWhatsAppContent(): self
    {
        return new self('WhatsApp message must have a content type set (text, image, document, location, or interactive).');
    }

    public static function invalidLatitude(float $value): self
    {
        return new self("Invalid latitude value: $value. Must be between -90 and 90.");
    }

    public static function invalidLongitude(float $value): self
    {
        return new self("Invalid longitude value: $value. Must be between -180 and 180.");
    }

    public static function allRecipientsInvalid(): self
    {
        return new self('All recipient numbers are invalid. No API call was made.');
    }
}
