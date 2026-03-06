<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\DltManagerType;

final class DltManagerTypeTest extends TestCase
{
    #[Test]
    public function it_has_expected_backed_values(): void
    {
        $this->assertSame('sender', DltManagerType::SENDER->value);
        $this->assertSame('template', DltManagerType::TEMPLATE->value);
    }

    #[Test]
    public function it_can_be_created_from_value(): void
    {
        $this->assertSame(DltManagerType::SENDER, DltManagerType::from('sender'));
        $this->assertSame(DltManagerType::TEMPLATE, DltManagerType::from('template'));
    }

    #[Test]
    public function it_returns_null_for_invalid_value(): void
    {
        $this->assertNull(DltManagerType::tryFrom('invalid'));
    }

    #[Test]
    public function it_has_exactly_two_cases(): void
    {
        $this->assertCount(2, DltManagerType::cases());
    }
}
