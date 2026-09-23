<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Webhooks;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\DeliveryStatus;
use Shakil\Fast2sms\Webhooks\WebhookHandler;

final class WebhookHandlerTest extends TestCase
{
    #[Test]
    public function it_parses_a_standard_payload(): void
    {
        $response = (new WebhookHandler())->parse([
            'request_id' => 'req_1',
            'status' => 'delivered',
            'mobile' => '9999999999',
        ]);

        $this->assertSame(DeliveryStatus::Delivered, $response->status);
        $this->assertSame('req_1', $response->requestId);
        $this->assertSame('9999999999', $response->mobile);
    }

    #[Test]
    public function it_flattens_a_clevertap_payload(): void
    {
        $response = (new WebhookHandler())->parse([
            'payloadVersion' => '1',
            'statuses' => [
                [
                    'msgId' => 'Oabcdef12345678',
                    'status' => 'delivered',
                    'timestamp' => '1718712002',
                    'description' => 'Delivered successfully',
                ],
            ],
        ]);

        $this->assertSame(DeliveryStatus::Delivered, $response->status);
        $this->assertSame('Oabcdef12345678', $response->requestId);
        $this->assertSame(1718712002, $response->deliveryTimestamp);
        $this->assertSame('Delivered successfully', $response->getMessage());
    }
}
