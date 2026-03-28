<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\DataTransferObjects;

use Error;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;

final class SmsParametersTest extends TestCase
{
    #[Test]
    public function it_creates_with_required_fields(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Hello World',
            route: SmsRoute::QUICK,
        );

        $this->assertSame(['9876543210'], $params->numbers);
        $this->assertSame('Hello World', $params->message);
        $this->assertSame(SmsRoute::QUICK, $params->route);
    }

    #[Test]
    public function it_defaults_optional_fields_to_null_or_false(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Test',
            route: SmsRoute::QUICK,
        );

        $this->assertNull($params->language);
        $this->assertNull($params->senderId);
        $this->assertNull($params->entityId);
        $this->assertNull($params->templateId);
        $this->assertNull($params->variablesValues);
        $this->assertFalse($params->flash);
        $this->assertNull($params->scheduleTime);
    }

    #[Test]
    public function it_stores_all_optional_fields(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210', '9123456789'],
            message: 'OTP: {#var#}',
            route: SmsRoute::DLT,
            language: SmsLanguage::UNICODE,
            senderId: 'SENDER',
            entityId: 'ENT123',
            templateId: 'TPL456',
            variablesValues: '123456',
            flash: true,
            scheduleTime: '2025-01-01-10-00',
        );

        $this->assertSame(['9876543210', '9123456789'], $params->numbers);
        $this->assertSame(SmsLanguage::UNICODE, $params->language);
        $this->assertSame('SENDER', $params->senderId);
        $this->assertSame('ENT123', $params->entityId);
        $this->assertSame('TPL456', $params->templateId);
        $this->assertSame('123456', $params->variablesValues);
        $this->assertTrue($params->flash);
        $this->assertSame('2025-01-01-10-00', $params->scheduleTime);
    }

    #[Test]
    public function it_is_immutable_after_construction(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Test',
            route: SmsRoute::QUICK,
        );

        $this->expectException(Error::class);

        $params->message = 'Modified'; // @phpstan-ignore-line
    }

    #[Test]
    public function it_accepts_multiple_numbers(): void
    {
        $numbers = ['9876543210', '9123456789', '9000000001'];
        $params = new SmsParameters(
            numbers: $numbers,
            message: 'Bulk SMS',
            route: SmsRoute::QUICK,
        );

        $this->assertCount(3, $params->numbers);
        $this->assertSame($numbers, $params->numbers);
    }

    #[Test]
    public function it_accepts_variables_values_as_array(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Template',
            route: SmsRoute::DLT,
            variablesValues: ['val1', 'val2'],
        );

        $this->assertSame(['val1', 'val2'], $params->variablesValues);
    }
}
