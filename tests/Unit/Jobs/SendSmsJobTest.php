<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Jobs;

use Error;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Jobs\SendSmsJob;

final class SendSmsJobTest extends TestCase
{
    #[Test]
    public function it_implements_should_queue(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Test',
            route: SmsRoute::QUICK,
        );

        $job = new SendSmsJob($params);

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    #[Test]
    public function it_stores_parameters(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Hello',
            route: SmsRoute::QUICK,
            language: SmsLanguage::ENGLISH,
            flash: true,
        );

        $job = new SendSmsJob($params);

        $this->assertSame($params, $job->parameters);
        $this->assertSame(['9876543210'], $job->parameters->numbers);
        $this->assertSame('Hello', $job->parameters->message);
        $this->assertSame(SmsRoute::QUICK, $job->parameters->route);
        $this->assertTrue($job->parameters->flash);
    }

    #[Test]
    public function it_parameters_are_readonly(): void
    {
        $params = new SmsParameters(
            numbers: ['9876543210'],
            message: 'Test',
            route: SmsRoute::QUICK,
        );

        $job = new SendSmsJob($params);

        $this->expectException(Error::class);

        $job->parameters = new SmsParameters( // @phpstan-ignore-line
            numbers: ['9999999999'],
            message: 'Modified',
            route: SmsRoute::OTP,
        );
    }
}
