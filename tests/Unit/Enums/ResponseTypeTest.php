<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\ResponseType;

final class ResponseTypeTest extends TestCase
{
    #[Test]
    public function it_has_expected_backed_values(): void
    {
        $this->assertSame('sms', ResponseType::Sms->value);
        $this->assertSame('wallet', ResponseType::WalletBalance->value);
        $this->assertSame('dlt_manager', ResponseType::DltManager->value);
        $this->assertSame('whatsapp', ResponseType::WhatsApp->value);
        $this->assertSame('generic', ResponseType::Generic->value);
    }

    #[Test]
    public function it_can_be_created_from_value(): void
    {
        $this->assertSame(ResponseType::Sms, ResponseType::from('sms'));
        $this->assertSame(ResponseType::WhatsApp, ResponseType::from('whatsapp'));
    }

    #[Test]
    public function it_returns_null_for_invalid_value(): void
    {
        $this->assertNull(ResponseType::tryFrom('invalid'));
    }

    #[Test]
    public function it_has_five_cases(): void
    {
        $this->assertCount(5, ResponseType::cases());
    }
}
