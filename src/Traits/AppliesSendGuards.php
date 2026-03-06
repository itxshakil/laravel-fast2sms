<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;
use Shakil\Fast2sms\Support\SendRateThrottle;

/**
 * Provides shared send-guard logic: deduplication, rate throttle, and balance gate.
 */
trait AppliesSendGuards
{
    /**
     * Check all send guards and return a closure that commits the deduplication
     * cache entry. The caller MUST invoke the returned closure only after a
     * successful send so that a failed attempt does not block legitimate retries.
     *
     * @return Closure(): void Dedup commit callback (no-op when dedup is disabled).
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     * @throws ThrottleExceededException
     */
    protected function applySendGuards(string $dedupKey): Closure
    {
        $commitDedup = static function (): void {};

        if (config('fast2sms.deduplication.enabled', false)) {
            $hash = md5($dedupKey);
            $store = config('fast2sms.deduplication.store');
            $cache = $store ? Cache::store($store) : Cache::store();
            $key = 'fast2sms:dedup:' . $hash;

            if ($cache->has($key)) {
                throw DuplicateSendException::detected($hash);
            }

            $commitDedup = static function () use ($cache, $key): void {
                $cache->put($key, true, (int) config('fast2sms.deduplication.ttl', 60));
            };
        }

        if (config('fast2sms.throttle.enabled', false)) {
            (new SendRateThrottle())->check();
        }

        if (config('fast2sms.balance_gate.enabled', false)) {
            /** @var WalletBalanceResponse $balanceResponse */
            $balanceResponse = $this->executeApiCall([], '/wallet');
            $balance = (float) $balanceResponse->balance;
            $threshold = (float) config('fast2sms.balance_gate.threshold', 10.0);

            if ($balance <= $threshold) {
                if (config('fast2sms.events.enabled', true)) {
                    event(new LowBalanceDetected($balance, $threshold));
                }

                if (config('fast2sms.balance_gate.abort', true)) {
                    throw InsufficientBalanceException::belowThreshold($balance, $threshold);
                }
            }
        }

        return $commitDedup;
    }
}
