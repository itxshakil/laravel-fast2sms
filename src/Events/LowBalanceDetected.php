<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Events;

/**
 * Event triggered when the SMS balance falls below the defined threshold.
 */
readonly class LowBalanceDetected
{
    /**
     * Create a new LowBalanceDetected event instance.
     *
     * @param  float  $balance  The current SMS balance.
     * @param  float  $threshold  The balance threshold that triggered the event.
     */
    public function __construct(
        public float $balance,
        public float $threshold
    ) {}
}
