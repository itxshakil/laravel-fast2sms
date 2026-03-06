<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\DataTransferObjects;

use Error;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;

final class WhatsAppParametersTest extends TestCase
{
    #[Test]
    public function it_creates_with_required_to_field(): void
    {
        $params = new WhatsAppParameters(to: '9876543210');

        $this->assertSame('9876543210', $params->to);
    }

    #[Test]
    public function it_defaults_optional_fields_to_null(): void
    {
        $params = new WhatsAppParameters(to: '9876543210');

        $this->assertNull($params->phoneNumberId);
        $this->assertNull($params->type);
        $this->assertNull($params->body);
        $this->assertNull($params->templateId);
        $this->assertNull($params->variables);
        $this->assertNull($params->mediaUrl);
        $this->assertNull($params->documentFilename);
        $this->assertNull($params->components);
    }

    #[Test]
    public function it_stores_whatsapp_type_enum(): void
    {
        $params = new WhatsAppParameters(
            to: '9876543210',
            type: WhatsAppType::TEXT,
        );

        $this->assertSame(WhatsAppType::TEXT, $params->type);
    }

    #[Test]
    public function it_stores_all_optional_fields(): void
    {
        $params = new WhatsAppParameters(
            to: '9876543210',
            phoneNumberId: 'phone-123',
            type: WhatsAppType::TEXT,
            body: 'Hello {{1}}',
            templateId: 'tpl-abc',
            variables: ['World'],
            mediaUrl: 'https://example.com/image.jpg',
            documentFilename: 'doc.pdf',
            components: [['type' => 'body', 'parameters' => []]],
        );

        $this->assertSame('phone-123', $params->phoneNumberId);
        $this->assertSame(WhatsAppType::TEXT, $params->type);
        $this->assertSame('Hello {{1}}', $params->body);
        $this->assertSame('tpl-abc', $params->templateId);
        $this->assertSame(['World'], $params->variables);
        $this->assertSame('https://example.com/image.jpg', $params->mediaUrl);
        $this->assertSame('doc.pdf', $params->documentFilename);
        $this->assertCount(1, $params->components);
    }

    #[Test]
    public function it_accepts_array_of_recipients(): void
    {
        $params = new WhatsAppParameters(to: ['9876543210', '9123456789']);

        $this->assertSame(['9876543210', '9123456789'], $params->to);
    }

    #[Test]
    public function it_is_immutable_after_construction(): void
    {
        $params = new WhatsAppParameters(to: '9876543210');

        $this->expectException(Error::class);

        $params->body = 'Modified'; // @phpstan-ignore-line
    }
}
