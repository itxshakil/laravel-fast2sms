<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Events;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

final class EventsTest extends TestCase
{
    #[Test]
    public function it_sms_sent_carries_payload_and_response(): void
    {
        $payload = ['numbers' => '9876543210', 'message' => 'Hello'];
        $response = new SmsResponse(['return' => true, 'request_id' => 'REQ1']);

        $event = new SmsSent($payload, $response);

        $this->assertSame($payload, $event->payload);
        $this->assertSame($response, $event->response);
    }

    #[Test]
    public function it_sms_failed_carries_payload_and_exception(): void
    {
        $payload = ['numbers' => '9876543210'];
        $exception = new Fast2smsException('Send failed');

        $event = new SmsFailed($payload, $exception);

        $this->assertSame($payload, $event->payload);
        $this->assertSame($exception, $event->exception);
        $this->assertNull($event->response);
    }

    #[Test]
    public function it_sms_failed_carries_optional_response(): void
    {
        $payload = ['numbers' => '9876543210'];
        $exception = new Fast2smsException('Send failed');
        $responseData = ['return' => false, 'message' => ['Error sending SMS']];

        $event = new SmsFailed($payload, $exception, $responseData);

        $this->assertSame($responseData, $event->response);
    }

    #[Test]
    public function it_low_balance_detected_carries_balance_and_threshold(): void
    {
        $event = new LowBalanceDetected(balance: 10.5, threshold: 50.0);

        $this->assertSame(10.5, $event->balance);
        $this->assertSame(50.0, $event->threshold);
    }

    #[Test]
    public function it_whatsapp_sent_carries_payload_and_response(): void
    {
        $payload = ['to' => '9876543210', 'type' => 'text'];
        $response = new WhatsAppResponse(['return' => true, 'message' => 'Sent']);

        $event = new WhatsAppSent($payload, $response);

        $this->assertSame($payload, $event->payload);
        $this->assertSame($response, $event->response);
    }

    #[Test]
    public function it_whatsapp_failed_carries_payload_and_exception(): void
    {
        $payload = ['to' => '9876543210'];
        $exception = new Fast2smsException('WhatsApp send failed');

        $event = new WhatsAppFailed($payload, $exception);

        $this->assertSame($payload, $event->payload);
        $this->assertSame($exception, $event->exception);
        $this->assertNull($event->response);
    }

    #[Test]
    public function it_whatsapp_failed_carries_optional_response(): void
    {
        $payload = ['to' => '9876543210'];
        $exception = new Fast2smsException('WhatsApp send failed');
        $responseData = ['return' => false, 'message' => 'Error'];

        $event = new WhatsAppFailed($payload, $exception, $responseData);

        $this->assertSame($responseData, $event->response);
    }
}
