<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Responses;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Responses\DltManagerResponse;

final class DltManagerResponseTest extends TestCase
{
    #[Test]
    public function it_returns_empty_data_when_absent(): void
    {
        $response = new DltManagerResponse(['return' => true]);

        $this->assertSame([], $response->getData());
    }

    #[Test]
    public function it_returns_data_array(): void
    {
        $data = [
            ['sender_id' => 'SENDER', 'entity_id' => 'ENT1', 'entity_name' => 'Company'],
        ];
        $response = new DltManagerResponse(['return' => true, 'data' => $data]);

        $this->assertSame($data, $response->getData());
    }

    #[Test]
    public function it_returns_formatted_senders(): void
    {
        $response = new DltManagerResponse([
            'return' => true,
            'data' => [
                ['sender_id' => 'SENDER1', 'entity_id' => 'ENT1', 'entity_name' => 'Company A'],
                ['sender_id' => 'SENDER2', 'entity_id' => 'ENT2', 'entity_name' => 'Company B'],
            ],
        ]);

        $senders = $response->getSenders();

        $this->assertCount(2, $senders);
        $this->assertSame('SENDER1', $senders[0]['sender_id']);
        $this->assertSame('ENT1', $senders[0]['entity_id']);
        $this->assertSame('Company A', $senders[0]['entity_name']);
    }

    #[Test]
    public function it_returns_templates_from_data(): void
    {
        $templates = [['id' => 'TPL1', 'name' => 'OTP Template']];
        $response = new DltManagerResponse([
            'return' => true,
            'data' => [
                ['templates' => $templates],
            ],
        ]);

        $this->assertSame($templates, $response->getTemplates());
    }

    #[Test]
    public function it_returns_empty_templates_when_absent(): void
    {
        $response = new DltManagerResponse(['return' => true, 'data' => []]);

        $this->assertSame([], $response->getTemplates());
    }

    #[Test]
    public function it_merges_templates_from_multiple_data_items(): void
    {
        $response = new DltManagerResponse([
            'return' => true,
            'data' => [
                ['templates' => [['id' => 'TPL1']]],
                ['templates' => [['id' => 'TPL2'], ['id' => 'TPL3']]],
            ],
        ]);

        $this->assertCount(3, $response->getTemplates());
    }

    #[Test]
    public function it_is_success_when_return_is_true(): void
    {
        $response = new DltManagerResponse(['return' => true]);

        $this->assertTrue($response->isSuccess());
    }
}
