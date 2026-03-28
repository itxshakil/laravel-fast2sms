<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

enum SmsRoute: string
{
    case DLT = 'dlt';

    case OTP = 'otp';

    case QUICK = 'q';

    case DLT_MANUAL = 'dlt_manual';
}
