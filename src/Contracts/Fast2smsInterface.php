<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

/**
 * Defines the contract for the Fast2sms service.
 */
interface Fast2smsInterface extends AccountManagerInterface, SmsSenderInterface {}
