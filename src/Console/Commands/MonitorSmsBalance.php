<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Console\Commands;

use Illuminate\Console\Command;
use JsonException;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;

/**
 * Command to monitor SMS balance and dispatch an event if it falls below a set threshold.
 */
class MonitorSmsBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fast2sms:balance
                          {--threshold= : The balance threshold that triggers the alert (default: fast2sms.balance_threshold config)}
                          {--json : Output the result as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor SMS balance and dispatch event if it falls below threshold';

    /**
     * Execute the console command.
     *
     * Checks the current SMS balance from Fast2SMS, compares it with the given
     * threshold, and dispatches a LowBalanceDetected event if it is below threshold.
     *
     * @return int Exit code: self::SUCCESS on success, self::FAILURE on error.
     *
     * @throws JsonException
     */
    public function handle(): int
    {
        $threshold = $this->getThreshold();

        try {
            /** @var WalletBalanceResponse $response */
            $response = Fast2sms::checkBalance();

            return $this->handleBalance($response->balance, $threshold, (bool) $this->option('json'));

        } catch (Fast2smsException $e) {
            if ($this->option('json')) {
                $this->line(json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR));
            } else {
                $this->components->error("Failed to check SMS balance: {$e->getMessage()}");
            }

            return self::FAILURE;
        }
    }

    /**
     * @throws JsonException
     */
    public function handleBalance(?float $balance, float $threshold, bool $jsonMode = false): int
    {
        $belowThreshold = $balance !== null && $balance <= $threshold;

        if ($jsonMode) {
            $this->line(json_encode([
                'balance' => $balance,
                'threshold' => $threshold,
                'below_threshold' => $belowThreshold,
            ], JSON_THROW_ON_ERROR));
        } else {
            $balanceFormatted = number_format((float) $balance, 2);
            $thresholdFormatted = number_format($threshold, 2);

            if ($this->components !== null) {
                $this->components->info("Wallet balance: ₹$balanceFormatted");

                if ($belowThreshold) {
                    $this->components->warn("Balance is below threshold of ₹$thresholdFormatted");
                }
            } else {
                $this->line("Wallet balance: ₹$balanceFormatted");

                if ($belowThreshold) {
                    $this->line("Balance is below threshold of ₹$thresholdFormatted");
                }
            }
        }

        if ($belowThreshold) {
            if (config('fast2sms.events.enabled', true)) {
                event(new LowBalanceDetected($balance, $threshold));
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Get the SMS balance threshold from the command option or configuration.
     *
     * @return float The balance threshold value.
     */
    private function getThreshold(): float
    {
        return (float) ($this->option('threshold')
            ?? config('fast2sms.balance_threshold', 1000));
    }
}
