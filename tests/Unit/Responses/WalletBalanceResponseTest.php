<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Responses;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;

final class WalletBalanceResponseTest extends TestCase
{
    #[Test]
    public function it_carries_wallet_balance(): void
    {
        $response = new WalletBalanceResponse(['return' => true, 'wallet' => '123.45']);

        $this->assertSame(123.45, $response->balance);
    }

    #[Test]
    public function it_carries_sms_count(): void
    {
        $response = new WalletBalanceResponse(['return' => true, 'sms_count' => '500']);

        $this->assertSame(500, $response->smsCount);
    }

    #[Test]
    public function it_returns_null_balance_when_absent(): void
    {
        $response = new WalletBalanceResponse(['return' => true]);

        $this->assertNull($response->balance);
    }

    #[Test]
    public function it_returns_null_sms_count_when_absent(): void
    {
        $response = new WalletBalanceResponse(['return' => true]);

        $this->assertNull($response->smsCount);
    }

    #[Test]
    public function it_is_success_when_return_is_true(): void
    {
        $response = new WalletBalanceResponse(['return' => true, 'wallet' => '50.00']);

        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_to_array_includes_all_data(): void
    {
        $data = ['return' => true, 'wallet' => '100.00', 'sms_count' => '200'];
        $response = new WalletBalanceResponse($data);

        $this->assertSame($data, $response->toArray());
    }
}
