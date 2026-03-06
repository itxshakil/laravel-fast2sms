<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Testing;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Testing\RecordedWhatsAppSend;

class RecordedWhatsAppSendTest extends TestCase
{
    public function test_it_stores_parameters_and_sent_at(): void
    {
        $parameters = new WhatsAppParameters(
            to: '9999999999',
            type: WhatsAppType::TEXT,
            body: 'Hello',
        );
        $sentAt = new DateTimeImmutable('2024-01-01 12:00:00');

        $recorded = new RecordedWhatsAppSend($parameters, $sentAt);

        $this->assertSame($parameters, $recorded->parameters);
        $this->assertSame($sentAt, $recorded->sentAt);
    }

    public function test_parameters_fields_are_accessible(): void
    {
        $parameters = new WhatsAppParameters(
            to: '8888888888',
            type: WhatsAppType::IMAGE,
            body: null,
            templateId: 'tmpl_001',
        );
        $sentAt = new DateTimeImmutable();

        $recorded = new RecordedWhatsAppSend($parameters, $sentAt);

        $this->assertSame('8888888888', $recorded->parameters->to);
        $this->assertSame(WhatsAppType::IMAGE, $recorded->parameters->type);
        $this->assertNull($recorded->parameters->body);
        $this->assertSame('tmpl_001', $recorded->parameters->templateId);
    }

    public function test_sent_at_reflects_the_time_provided(): void
    {
        $parameters = new WhatsAppParameters(
            to: '9999999999',
            type: WhatsAppType::TEXT,
        );
        $sentAt = new DateTimeImmutable('2025-06-15 09:30:00');

        $recorded = new RecordedWhatsAppSend($parameters, $sentAt);

        $this->assertSame('2025-06-15 09:30:00', $recorded->sentAt->format('Y-m-d H:i:s'));
    }

    public function test_to_can_be_an_array_of_numbers(): void
    {
        $parameters = new WhatsAppParameters(
            to: ['9999999999', '8888888888'],
            type: WhatsAppType::TEXT,
            body: 'Broadcast',
        );
        $sentAt = new DateTimeImmutable();

        $recorded = new RecordedWhatsAppSend($parameters, $sentAt);

        $this->assertSame(['9999999999', '8888888888'], $recorded->parameters->to);
    }
}
