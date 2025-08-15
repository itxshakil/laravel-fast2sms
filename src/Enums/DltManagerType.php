<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Enums;

/**
 * Enum representing the types of DLT (Distributed Ledger Technology) manager in Fast2sms.
 *
 * This enum is used to specify the type of DLT manager data when retrieving
 * information through the Fast2sms API's DLT manager endpoint.
 *
 * Usage example:
 * ```php
 * $type = DltManagerType::SENDER;
 * $dltInfo = $fast2sms->dltManager($type->value);
 * ```
 *
 * @see \Shakil\Fast2sms\Fast2sms::dltManager()
 */
enum DltManagerType: string
{
    /**
     * Represents the sender type DLT manager.
     *
     * Used when retrieving information about registered sender IDs
     * and their associated details in the DLT platform.
     */
    case SENDER = 'sender';

    /**
     * Represents the template type DLT manager.
     *
     * Used when retrieving information about registered templates
     * and their associated template details in the DLT platform.
     */
    case TEMPLATE = 'template';
}
