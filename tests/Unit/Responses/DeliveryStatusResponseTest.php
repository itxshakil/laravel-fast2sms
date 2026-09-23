<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Responses;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\DeliveryStatus;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;

final class DeliveryStatusResponseTest extends TestCase
{
    #[Test]
    public function it_maps_a_standard_delivered_payload(): void
    {
        $response = DeliveryStatusResponse::fromPayload($this->standardPayload());

        $this->assertSame(DeliveryStatus::Delivered, $response->status);
        $this->assertSame('Oabcdef12345678', $response->requestId);
        $this->assertSame('9999999999', $response->mobile);
        $this->assertSame('sms', $response->channel);
        $this->assertSame('0.2000', $response->amountDebited);
        $this->assertSame(1718712002, $response->deliveryTimestamp);
        $this->assertSame(1, $response->postAttempt);
        $this->assertTrue($response->isSuccess());
        $this->assertSame('Delivered successfully', $response->getMessage());
    }

    #[Test]
    public function it_maps_a_failed_payload(): void
    {
        $payload = $this->standardPayload();
        $payload['status'] = 'failed';
        $payload['failure_reason'] = 'DND number';

        $response = DeliveryStatusResponse::fromPayload($payload);

        $this->assertSame(DeliveryStatus::Failed, $response->status);
        $this->assertFalse($response->isSuccess());
        $this->assertSame('DND number', $response->failureReason);
    }

    #[Test]
    public function it_falls_back_to_recipient_id_for_mobile(): void
    {
        $payload = $this->standardPayload();
        unset($payload['mobile']);
        $payload['recipient_id'] = '918888888888';

        $response = DeliveryStatusResponse::fromPayload($payload);

        $this->assertSame('918888888888', $response->mobile);
    }

    #[Test]
    public function it_defaults_post_attempt_to_one_and_status_to_pending(): void
    {
        $response = DeliveryStatusResponse::fromPayload(['request_id' => 'X']);

        $this->assertSame(DeliveryStatus::Pending, $response->status);
        $this->assertSame(1, $response->postAttempt);
        $this->assertNull($response->deliveryTimestamp);
    }

    #[Test]
    public function it_exposes_raw_data(): void
    {
        $payload = $this->standardPayload();
        $response = DeliveryStatusResponse::fromPayload($payload);

        $this->assertSame($payload, $response->getRawData());
        $this->assertSame($payload, $response->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    private function standardPayload(): array
    {
        return [
            'request_id' => 'Oabcdef12345678',
            'route' => 'otp',
            'mobile' => '9999999999',
            'status' => 'delivered',
            'status_description' => 'Delivered successfully',
            'amount_debited' => '0.2000',
            'delivery_timestamp' => 1718712002,
            'post_attempt' => 1,
            'channel' => 'sms',
        ];
    }
}
