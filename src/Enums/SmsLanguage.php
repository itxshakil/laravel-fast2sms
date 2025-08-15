<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

/**
 * Enum representing the available SMS languages for Fast2sms.
 */
enum SmsLanguage: string
{
    /**
     * English language for SMS.
     */
    case ENGLISH = 'english';

    /**
     * Unicode language for SMS, used for regional languages.
     */
    case UNICODE = 'unicode';
}
