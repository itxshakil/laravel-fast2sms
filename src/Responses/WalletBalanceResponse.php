<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

class WalletBalanceResponse extends Fast2smsResponse
{
    public readonly ?float $balance;

    public readonly ?int $smsCount;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->balance = isset($data['wallet']) ? (float) $data['wallet'] : null;
        $this->smsCount = isset($data['sms_count']) ? (int) $data['sms_count'] : null;
    }
}
