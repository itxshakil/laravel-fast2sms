<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Exceptions;

/**
 * Thrown when the wallet balance is below the configured threshold and abort is enabled.
 */
class InsufficientBalanceException extends Fast2smsException
{
    public static function belowThreshold(float $balance, float $threshold): self
    {
        return new self("Insufficient balance: current balance ₹$balance is below the threshold of ₹$threshold.");
    }
}
