<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Enums\DltManagerType;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;

trait ManagesAccount
{
    /**
     * Retrieve the wallet balance from Fast2sms.
     *
     * @param float|null $threshold Optional threshold to check for low balance
     */
    public function checkBalance(?float $threshold = null): ResponseInterface
    {
        /** @var WalletBalanceResponse $response */
        $response = $this->executeApiCall([], '/wallet');

        if ($threshold !== null) {
            $balance = $response->balance;
            if (($balance <= $threshold) && config('fast2sms.events.enabled', true)) {
                event(new LowBalanceDetected($balance, $threshold));
            }
        }

        return $response;
    }

    /**
     * Retrieve DLT manager details from Fast2sms.
     *
     * @param DltManagerType $type The type of DLT manager data ('sender' or 'template').
     *
     * @throws Fast2smsException If validation fails or API call fails.
     */
    public function dltManager(DltManagerType $type): ResponseInterface
    {
        if (empty($this->config->apiKey)) {
            throw new Fast2smsException('Fast2sms API Key is not configured.');
        }

        return $this->executeApiCall(['type' => $type->value], '/dlt_manager');
    }
}
