<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Responses;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Responses\SmsResponse;

final class SmsResponseTest extends TestCase
{
    #[Test]
    public function it_carries_request_id(): void
    {
        $response = new SmsResponse(['return' => true, 'request_id' => 'REQ123', 'message' => []]);

        $this->assertSame('REQ123', $response->getRequestId());
    }

    #[Test]
    public function it_returns_null_request_id_when_absent(): void
    {
        $response = new SmsResponse(['return' => true]);

        $this->assertNull($response->getRequestId());
    }

    #[Test]
    public function it_carries_messages_array(): void
    {
        // message key must be a flat string array for Fast2smsResponse::message() to work
        $messages = ['Message sent successfully'];
        $response = new SmsResponse(['return' => true, 'request_id' => 'REQ1', 'message' => $messages]);

        $this->assertSame($messages, $response->getMessages());
    }

    #[Test]
    public function it_returns_empty_array_when_messages_absent(): void
    {
        $response = new SmsResponse(['return' => true]);

        $this->assertSame([], $response->getMessages());
    }

    #[Test]
    public function it_is_success_when_return_is_true(): void
    {
        $response = new SmsResponse(['return' => true]);

        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_is_not_success_when_return_is_false(): void
    {
        $response = new SmsResponse(['return' => false]);

        $this->assertFalse($response->isSuccess());
    }

    #[Test]
    public function it_to_array_includes_all_data(): void
    {
        $data = ['return' => true, 'request_id' => 'REQ123', 'message' => []];
        $response = new SmsResponse($data);

        $this->assertSame($data, $response->toArray());
    }
}
