<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\PayloadBuilders;

use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\PayloadBuilders\DltPayloadBuilder;

class DltPayloadBuilderTest extends TestCase
{
    public function test_it_builds_dlt_payload_with_all_required_fields(): void
    {
        $sms = new class
        {
            public function getSenderId(): string
            {
                return 'SENDER';
            }

            public function getMessage(): string
            {
                return 'TPL001';
            }

            public function getEntityId(): ?string
            {
                return null;
            }

            public function getTemplateId(): string
            {
                return 'TPL001';
            }

            public function getVariablesValues(): string
            {
                return 'John|OTP123';
            }
        };

        $payload = (new DltPayloadBuilder())->build($sms);

        $this->assertSame('SENDER', $payload['sender_id']);
        $this->assertSame('TPL001', $payload['message']);
        $this->assertSame('TPL001', $payload['template_id']);
        $this->assertSame('John|OTP123', $payload['variables_values']);
    }

    public function test_it_includes_entity_id_when_provided(): void
    {
        $sms = new class
        {
            public function getSenderId(): string
            {
                return 'SENDER';
            }

            public function getMessage(): string
            {
                return 'TPL001';
            }

            public function getEntityId(): string
            {
                return 'ENT123';
            }

            public function getTemplateId(): string
            {
                return 'TPL001';
            }

            public function getVariablesValues(): string
            {
                return 'val1';
            }
        };

        $payload = (new DltPayloadBuilder())->build($sms);

        $this->assertSame('ENT123', $payload['entity_id']);
    }

    public function test_it_returns_all_expected_keys(): void
    {
        $sms = new class
        {
            public function getSenderId(): string
            {
                return 'SENDER';
            }

            public function getMessage(): string
            {
                return 'msg';
            }

            public function getEntityId(): ?string
            {
                return null;
            }

            public function getTemplateId(): string
            {
                return 'TPL';
            }

            public function getVariablesValues(): string
            {
                return 'val';
            }
        };

        $payload = (new DltPayloadBuilder())->build($sms);

        $this->assertArrayHasKey('sender_id', $payload);
        $this->assertArrayHasKey('message', $payload);
        $this->assertArrayHasKey('entity_id', $payload);
        $this->assertArrayHasKey('template_id', $payload);
        $this->assertArrayHasKey('variables_values', $payload);
        $this->assertCount(5, $payload);
    }
}
