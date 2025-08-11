<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Responses\Fast2smsResponse;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;

class Fast2smsResponseTest extends TestCase
{
    #[Test]
    public function it_can_create_a_successful_response_object(): void
    {
        $data = ['return' => true, 'message' => 'Success'];
        $response = new Fast2smsResponse($data);

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('Success', $response->getErrorMessage());
        $this->assertEquals($data, $response->toArray());
    }

    #[Test]
    public function it_can_create_a_failed_response_object(): void
    {
        $data = ['return' => false, 'message' => 'Failure', 'status_code' => 500];
        $response = new Fast2smsResponse($data);

        $this->assertFalse($response->isSuccess());
        $this->assertEquals('Failure', $response->getErrorMessage());
        $this->assertEquals(500, $response->getErrorCode());
    }

    #[Test]
    public function it_throws_an_exception_with_empty_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Fast2smsResponse([]);
    }

    #[Test]
    public function sms_response_can_get_request_id_and_messages(): void
    {
        $data = [
            'return' => true,
            'request_id' => 'abc-123',
            'message' => ['Message sent to 9999999999'],
        ];
        $response = new SmsResponse($data);

        $this->assertEquals('abc-123', $response->getRequestId());
        $this->assertEquals(['Message sent to 9999999999'], $response->getMessages());
    }

    #[Test]
    public function wallet_balance_response_can_get_balance_and_sms_count(): void
    {
        $data = ['return' => true, 'wallet' => '500.50', 'sms_count' => 1000];
        $response = new WalletBalanceResponse($data);

        $this->assertEquals(500.50, $response->balance);
        $this->assertEquals(1000, $response->smsCount);
    }
}
