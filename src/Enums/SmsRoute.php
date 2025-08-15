<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

/**
 * Enum representing the available SMS routes for Fast2sms.
 */
enum SmsRoute: string
{
    /**
     * DLT (Distributed Ledger Technology) route for transactional and promotional SMS.
     * Requires DLT registration, sender ID, and content templates.
     */
    case DLT = 'dlt';

    /**
     * OTP (One-Time Password) route for sending numeric OTPs.
     * Can be used without DLT registration for simple OTPs, but DLT approved OTPs are also supported.
     */
    case OTP = 'otp';

    /**
     * Quick SMS route for sending messages without DLT registration.
     * Uses international connectivity, has a random numeric sender ID, and higher cost.
     */
    case QUICK = 'q';

    /**
     * DLT Manual route for sending DLT approved SMS without Fast2SMS approval/verification.
     * Use with caution as Fast2SMS does not verify content for this route.
     */
    case DLT_MANUAL = 'dlt_manual';
}
