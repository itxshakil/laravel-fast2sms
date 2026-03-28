<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Messages;

use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Notifications\Messages\WhatsAppMessage;
use Shakil\Fast2sms\Tests\TestCase;

class WhatsAppMessageTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Fast2sms::fake();
    }

    #[Test]
    public function it_can_be_created_with_content(): void
    {
        $message = new WhatsAppMessage('Hello World');

        $this->assertEquals('Hello World', $message->content);
    }

    #[Test]
    public function it_can_set_content(): void
    {
        $message = new WhatsAppMessage;
        $message->content('Hello World');

        $this->assertEquals('Hello World', $message->content);
    }

    #[Test]
    public function it_can_set_recipient(): void
    {
        $message = new WhatsAppMessage;
        $message->to('9876543210');

        $this->assertEquals('9876543210', $message->to);
    }

    #[Test]
    public function it_can_set_type(): void
    {
        $message = new WhatsAppMessage;
        $message->type(WhatsAppType::IMAGE);

        $this->assertEquals(WhatsAppType::IMAGE, $message->type);
    }

    #[Test]
    public function it_can_set_template(): void
    {
        $message = new WhatsAppMessage;
        $message->template('tmpl_001', ['var1', 'var2']);

        $this->assertEquals('tmpl_001', $message->templateId);
        $this->assertEquals(['var1', 'var2'], $message->variables);
    }

    #[Test]
    public function it_can_set_template_with_integer_id(): void
    {
        $message = new WhatsAppMessage;
        $message->template(42, ['val1']);

        $this->assertEquals(42, $message->templateId);
        $this->assertEquals(['val1'], $message->variables);
    }

    #[Test]
    public function it_can_set_media_url(): void
    {
        $message = new WhatsAppMessage;
        $message->media('https://example.com/image.jpg');

        $this->assertEquals('https://example.com/image.jpg', $message->mediaUrl);
    }

    #[Test]
    public function it_can_set_document_filename(): void
    {
        $message = new WhatsAppMessage;
        $message->documentFilename('report.pdf');

        $this->assertEquals('report.pdf', $message->documentFilename);
    }

    #[Test]
    public function it_can_set_components(): void
    {
        $components = [
            ['type' => 'header', 'parameters' => [['type' => 'text', 'text' => 'Hello']]],
        ];

        $message = new WhatsAppMessage;
        $message->components($components);

        $this->assertEquals($components, $message->components);
    }

    #[Test]
    public function it_can_set_interactive_payload(): void
    {
        $interactive = ['type' => 'button', 'body' => ['text' => 'Pick one']];

        $message = new WhatsAppMessage;
        $message->interactive($interactive);

        $this->assertEquals($interactive, $message->interactive);
    }

    #[Test]
    public function it_can_set_location_payload(): void
    {
        $location = ['latitude' => 28.6139, 'longitude' => 77.2090, 'name' => 'Delhi'];

        $message = new WhatsAppMessage;
        $message->location($location);

        $this->assertEquals($location, $message->location);
    }

    #[Test]
    public function it_can_chain_methods(): void
    {
        $message = (new WhatsAppMessage)
            ->content('Hello')
            ->to('9876543210')
            ->type(WhatsAppType::TEXT)
            ->media('https://example.com/img.jpg')
            ->documentFilename('file.pdf');

        $this->assertEquals('Hello', $message->content);
        $this->assertEquals('9876543210', $message->to);
        $this->assertEquals(WhatsAppType::TEXT, $message->type);
        $this->assertEquals('https://example.com/img.jpg', $message->mediaUrl);
        $this->assertEquals('file.pdf', $message->documentFilename);
    }

    #[Test]
    public function it_can_create_text_message_via_static_factory(): void
    {
        $message = WhatsAppMessage::text('Hello');

        $this->assertEquals('Hello', $message->content);
        $this->assertEquals(WhatsAppType::TEXT, $message->type);
    }

    #[Test]
    public function it_can_create_image_message_via_static_factory(): void
    {
        $message = WhatsAppMessage::image('https://example.com/img.jpg', 'Caption');

        $this->assertEquals('https://example.com/img.jpg', $message->mediaUrl);
        $this->assertEquals('Caption', $message->content);
        $this->assertEquals(WhatsAppType::IMAGE, $message->type);
    }

    #[Test]
    public function it_can_create_document_message_via_static_factory(): void
    {
        $message = WhatsAppMessage::document('https://example.com/doc.pdf', 'doc.pdf');

        $this->assertEquals('https://example.com/doc.pdf', $message->mediaUrl);
        $this->assertEquals('doc.pdf', $message->documentFilename);
        $this->assertEquals(WhatsAppType::DOCUMENT, $message->type);
    }

    #[Test]
    public function it_can_create_location_message_via_static_factory(): void
    {
        $message = WhatsAppMessage::forLocation(28.6139, 77.2090, 'Delhi', 'India');

        $this->assertEquals(WhatsAppType::LOCATION, $message->type);
        $this->assertEquals(28.6139, $message->location['latitude']);
        $this->assertEquals(77.2090, $message->location['longitude']);
        $this->assertEquals('Delhi', $message->location['name']);
        $this->assertEquals('India', $message->location['address']);
    }

    #[Test]
    public function it_throws_on_invalid_latitude(): void
    {
        $this->expectException(ValidationException::class);

        WhatsAppMessage::forLocation(91.0, 77.2090);
    }

    #[Test]
    public function it_throws_on_invalid_longitude(): void
    {
        $this->expectException(ValidationException::class);

        WhatsAppMessage::forLocation(28.6139, 181.0);
    }

    #[Test]
    public function it_can_create_interactive_message_via_static_factory(): void
    {
        $payload = ['type' => 'button', 'body' => ['text' => 'Choose']];

        $message = WhatsAppMessage::forInteractive($payload);

        $this->assertEquals(WhatsAppType::INTERACTIVE, $message->type);
        $this->assertEquals($payload, $message->interactive);
    }

    #[Test]
    public function it_returns_human_readable_string(): void
    {
        $message = (new WhatsAppMessage)
            ->to('9876543210')
            ->type(WhatsAppType::TEXT);

        $this->assertStringContainsString('9876543210', (string) $message);
        $this->assertStringContainsString('text', (string) $message);
    }
}
