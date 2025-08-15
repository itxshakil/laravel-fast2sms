<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

/**
 * A class to handle wallet balance responses from the Fast2sms API.
 *
 * This class extends the base Fast2smsResponse and specifically handles
 * and formats the wallet balance and SMS count data returned by the API.
 */
class WalletBalanceResponse extends Fast2smsResponse
{
    /**
     * The current wallet balance.
     */
    public readonly ?float $balance;

    /**
     * The number of SMS messages available.
     */
    public readonly ?int $smsCount;

    /**
     * Creates a new WalletBalanceResponse instance.
     *
     * @param  array  $data  The raw response data from the API.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->balance = isset($data['wallet']) ? (float) $data['wallet'] : null;
        $this->smsCount = isset($data['sms_count']) ? (int) $data['sms_count'] : null;
    }
}
