<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\PayloadBuilders;

use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\PayloadBuilders\QuickPayloadBuilder;

class QuickPayloadBuilderTest extends TestCase
{
    public function test_it_builds_quick_payload_with_message_and_language(): void
    {
        $sms = new class
        {
            public function getMessage(): string
            {
                return 'Hello World';
            }

            public function getLanguage(): SmsLanguage
            {
                return SmsLanguage::ENGLISH;
            }
        };

        $payload = (new QuickPayloadBuilder())->build($sms);

        $this->assertSame('Hello World', $payload['message']);
        $this->assertSame(SmsLanguage::ENGLISH->value, $payload['language']);
    }

    public function test_it_builds_quick_payload_with_unicode_language(): void
    {
        $sms = new class
        {
            public function getMessage(): string
            {
                return 'नमस्ते';
            }

            public function getLanguage(): SmsLanguage
            {
                return SmsLanguage::UNICODE;
            }
        };

        $payload = (new QuickPayloadBuilder())->build($sms);

        $this->assertSame('नमस्ते', $payload['message']);
        $this->assertSame(SmsLanguage::UNICODE->value, $payload['language']);
    }

    public function test_it_only_returns_message_and_language_keys(): void
    {
        $sms = new class
        {
            public function getMessage(): string
            {
                return 'Test';
            }

            public function getLanguage(): SmsLanguage
            {
                return SmsLanguage::ENGLISH;
            }
        };

        $payload = (new QuickPayloadBuilder())->build($sms);

        $this->assertArrayHasKey('message', $payload);
        $this->assertArrayHasKey('language', $payload);
        $this->assertCount(2, $payload);
    }
}
