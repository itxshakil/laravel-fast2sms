<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Responses;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

final class WhatsAppResponseTest extends TestCase
{
    #[Test]
    public function it_is_success_when_return_is_true(): void
    {
        $response = new WhatsAppResponse(['return' => true]);

        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_is_success_when_success_key_is_true(): void
    {
        $response = new WhatsAppResponse(['success' => true]);

        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_is_success_when_status_key_is_true(): void
    {
        $response = new WhatsAppResponse(['status' => true]);

        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_is_not_success_when_return_is_false(): void
    {
        $response = new WhatsAppResponse(['return' => false]);

        $this->assertFalse($response->isSuccess());
    }

    #[Test]
    public function it_extracts_string_message(): void
    {
        $response = new WhatsAppResponse(['return' => true, 'message' => 'Message sent']);

        $this->assertSame('Message sent', $response->getMessage());
    }

    #[Test]
    public function it_extracts_error_message_from_error_key(): void
    {
        $response = new WhatsAppResponse(['return' => false, 'error' => ['message' => 'Invalid token']]);

        $this->assertSame('Invalid token', $response->getMessage());
    }

    #[Test]
    public function it_throws_for_empty_data(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new WhatsAppResponse([]);
    }

    #[Test]
    public function it_to_array_returns_raw_data(): void
    {
        $data = ['return' => true, 'message' => 'OK'];
        $response = new WhatsAppResponse($data);

        $this->assertSame($data, $response->toArray());
    }
}
