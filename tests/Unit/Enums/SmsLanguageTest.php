<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\SmsLanguage;

final class SmsLanguageTest extends TestCase
{
    #[Test]
    public function it_has_expected_backed_values(): void
    {
        $this->assertSame('english', SmsLanguage::ENGLISH->value);
        $this->assertSame('unicode', SmsLanguage::UNICODE->value);
    }

    #[Test]
    public function it_can_be_created_from_value(): void
    {
        $this->assertSame(SmsLanguage::ENGLISH, SmsLanguage::from('english'));
        $this->assertSame(SmsLanguage::UNICODE, SmsLanguage::from('unicode'));
    }

    #[Test]
    public function it_returns_null_for_invalid_value(): void
    {
        $this->assertNull(SmsLanguage::tryFrom('invalid'));
    }

    #[Test]
    public function it_has_exactly_two_cases(): void
    {
        $this->assertCount(2, SmsLanguage::cases());
    }
}
