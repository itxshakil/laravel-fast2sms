<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\DeliveryStatus;

final class DeliveryStatusTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string|null, 1: DeliveryStatus}>
     */
    public static function rawStatuses(): iterable
    {
        yield 'delivered' => ['delivered', DeliveryStatus::Delivered];
        yield 'delivered mixed case' => ['Delivered', DeliveryStatus::Delivered];
        yield 'success' => ['success', DeliveryStatus::Delivered];
        yield 'failed' => ['failed', DeliveryStatus::Failed];
        yield 'undelivered' => ['undelivered', DeliveryStatus::Failed];
        yield 'rejected' => ['rejected', DeliveryStatus::Failed];
        yield 'unknown' => ['queued', DeliveryStatus::Pending];
        yield 'empty' => ['', DeliveryStatus::Pending];
        yield 'null' => [null, DeliveryStatus::Pending];
    }

    #[Test]
    #[DataProvider('rawStatuses')]
    public function it_maps_raw_status_strings(?string $raw, DeliveryStatus $expected): void
    {
        $this->assertSame($expected, DeliveryStatus::fromRaw($raw));
    }

    #[Test]
    public function it_exposes_terminal_helpers(): void
    {
        $this->assertTrue(DeliveryStatus::Delivered->isDelivered());
        $this->assertFalse(DeliveryStatus::Delivered->isFailed());
        $this->assertTrue(DeliveryStatus::Failed->isFailed());
        $this->assertFalse(DeliveryStatus::Pending->isDelivered());
    }
}
