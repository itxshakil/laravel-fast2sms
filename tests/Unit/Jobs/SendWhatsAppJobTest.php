<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Jobs;

use Error;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Jobs\SendWhatsAppJob;

final class SendWhatsAppJobTest extends TestCase
{
    #[Test]
    public function it_implements_should_queue(): void
    {
        $params = new WhatsAppParameters(to: '9876543210');
        $job = new SendWhatsAppJob($params);

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    #[Test]
    public function it_stores_parameters(): void
    {
        $params = new WhatsAppParameters(
            to: '9876543210',
            type: WhatsAppType::TEXT,
            body: 'Hello World',
            templateId: 'tpl-123',
        );

        $job = new SendWhatsAppJob($params);

        $this->assertSame($params, $job->parameters);
        $this->assertSame('9876543210', $job->parameters->to);
        $this->assertSame(WhatsAppType::TEXT, $job->parameters->type);
        $this->assertSame('Hello World', $job->parameters->body);
        $this->assertSame('tpl-123', $job->parameters->templateId);
    }

    #[Test]
    public function it_parameters_are_readonly(): void
    {
        $params = new WhatsAppParameters(to: '9876543210');
        $job = new SendWhatsAppJob($params);

        $this->expectException(Error::class);

        $job->parameters = new WhatsAppParameters(to: '9999999999'); // @phpstan-ignore-line
    }

    #[Test]
    public function it_accepts_array_of_recipients_in_parameters(): void
    {
        $params = new WhatsAppParameters(to: ['9876543210', '9123456789']);
        $job = new SendWhatsAppJob($params);

        $this->assertSame(['9876543210', '9123456789'], $job->parameters->to);
    }
}
