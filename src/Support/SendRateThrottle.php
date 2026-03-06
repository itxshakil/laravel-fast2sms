<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Support;

use Illuminate\Support\Facades\RateLimiter;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;

/**
 * Sliding-window per-minute send-rate throttle backed by Laravel's RateLimiter.
 */
class SendRateThrottle
{
    private const string THROTTLE_KEY = 'fast2sms:throttle';

    /**
     * Atomically increment the per-minute counter and throw if the limit is exceeded.
     *
     * @throws ThrottleExceededException
     */
    public function check(): void
    {
        $max = (int) config('fast2sms.throttle.max_per_minute', 60);

        if (RateLimiter::tooManyAttempts(self::THROTTLE_KEY, $max)) {
            $count = RateLimiter::attempts(self::THROTTLE_KEY);
            throw ThrottleExceededException::limitReached($count, $max);
        }

        RateLimiter::hit(self::THROTTLE_KEY, 60);
    }
}
