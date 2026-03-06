<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\PayloadBuilders;

use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\PayloadBuilders\OtpPayloadBuilder;

class OtpPayloadBuilderTest extends TestCase
{
    public function test_it_builds_otp_payload_with_variables_values(): void
    {
        $sms = new class
        {
            public function getMessage(): string
            {
                return '123456';
            }
        };

        $payload = (new OtpPayloadBuilder())->build($sms);

        $this->assertSame('123456', $payload['variables_values']);
    }

    public function test_it_only_returns_variables_values_key(): void
    {
        $sms = new class
        {
            public function getMessage(): string
            {
                return '999888';
            }
        };

        $payload = (new OtpPayloadBuilder())->build($sms);

        $this->assertArrayHasKey('variables_values', $payload);
        $this->assertCount(1, $payload);
    }
}
