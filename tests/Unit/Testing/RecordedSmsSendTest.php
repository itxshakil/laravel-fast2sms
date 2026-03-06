<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Testing;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Testing\RecordedSmsSend;

class RecordedSmsSendTest extends TestCase
{
    public function test_it_stores_parameters_and_sent_at(): void
    {
        $parameters = new SmsParameters(
            numbers: ['9999999999'],
            message: 'Hello',
            route: SmsRoute::QUICK,
        );
        $sentAt = new DateTimeImmutable('2024-01-01 12:00:00');

        $recorded = new RecordedSmsSend($parameters, $sentAt);

        $this->assertSame($parameters, $recorded->parameters);
        $this->assertSame($sentAt, $recorded->sentAt);
    }

    public function test_parameters_numbers_are_accessible(): void
    {
        $parameters = new SmsParameters(
            numbers: ['9999999999', '8888888888'],
            message: 'Test',
            route: SmsRoute::OTP,
        );
        $sentAt = new DateTimeImmutable();

        $recorded = new RecordedSmsSend($parameters, $sentAt);

        $this->assertSame(['9999999999', '8888888888'], $recorded->parameters->numbers);
        $this->assertSame('Test', $recorded->parameters->message);
        $this->assertSame(SmsRoute::OTP, $recorded->parameters->route);
    }

    public function test_sent_at_reflects_the_time_provided(): void
    {
        $parameters = new SmsParameters(
            numbers: ['9999999999'],
            message: 'Hello',
            route: SmsRoute::QUICK,
        );
        $sentAt = new DateTimeImmutable('2025-06-15 09:30:00');

        $recorded = new RecordedSmsSend($parameters, $sentAt);

        $this->assertSame('2025-06-15 09:30:00', $recorded->sentAt->format('Y-m-d H:i:s'));
    }
}
