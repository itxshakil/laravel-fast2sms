<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Console\Commands;

use Illuminate\Console\Command;
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
    protected $signature = 'sms:monitor
                          {--threshold= : The balance threshold that triggers the alert}';

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
     */
    public function handle(): int
    {
        $threshold = $this->getThreshold();
        try {
            /** @var WalletBalanceResponse $response */
            $response = Fast2sms::checkBalance();

            $this->handleBalance($response->balance, $threshold);

            return self::SUCCESS;

        } catch (Fast2smsException $e) {
            $this->error("Failed to check SMS balance: {$e->getMessage()}");

            return self::FAILURE;
        }
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

    public function handleBalance(?float $balance, float $threshold): void
    {
        $this->info("Current SMS balance: ₹$balance");

        if ($balance <= $threshold) {
            event(new LowBalanceDetected($balance, $threshold));
            $this->warn("Balance (₹$balance) is below threshold (₹$threshold)");
        }
    }
}
