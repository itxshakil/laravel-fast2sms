<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\WhatsAppType;

final class WhatsAppTypeTest extends TestCase
{
    #[Test]
    public function it_has_expected_backed_values(): void
    {
        $this->assertSame('text', WhatsAppType::TEXT->value);
        $this->assertSame('image', WhatsAppType::IMAGE->value);
        $this->assertSame('document', WhatsAppType::DOCUMENT->value);
        $this->assertSame('audio', WhatsAppType::AUDIO->value);
        $this->assertSame('video', WhatsAppType::VIDEO->value);
        $this->assertSame('sticker', WhatsAppType::STICKER->value);
        $this->assertSame('location', WhatsAppType::LOCATION->value);
        $this->assertSame('reaction', WhatsAppType::REACTION->value);
        $this->assertSame('interactive', WhatsAppType::INTERACTIVE->value);
    }

    #[Test]
    public function it_can_be_created_from_value(): void
    {
        $this->assertSame(WhatsAppType::TEXT, WhatsAppType::from('text'));
        $this->assertSame(WhatsAppType::INTERACTIVE, WhatsAppType::from('interactive'));
    }

    #[Test]
    public function it_returns_null_for_invalid_value(): void
    {
        $this->assertNull(WhatsAppType::tryFrom('invalid'));
    }

    #[Test]
    public function it_has_nine_cases(): void
    {
        $this->assertCount(9, WhatsAppType::cases());
    }
}
