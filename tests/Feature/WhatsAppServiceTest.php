<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Tests\TestCase;
use Shakil\Fast2sms\WhatsApp;

class WhatsAppServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'fast2sms.api_key' => 'test-api-key',
            'fast2sms.whatsapp.default_phone_number_id' => '123456',
            'fast2sms.whatsapp.default_waba_id' => '654321',
            'fast2sms.whatsapp.version' => 'v24.0',
        ]);
    }

    public function test_whatsapp_instance_implements_whatsapp_interface(): void
    {
        $whatsapp = Fast2sms::whatsapp();

        $this->assertInstanceOf(WhatsAppInterface::class, $whatsapp);
        $this->assertInstanceOf(WhatsApp::class, $whatsapp);
    }

    public function test_to_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->to('919999999999');

        $this->assertSame($whatsapp, $result);
    }

    public function test_from_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->from('999888777');

        $this->assertSame($whatsapp, $result);
    }

    public function test_type_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->type(WhatsAppType::TEXT);

        $this->assertSame($whatsapp, $result);
    }

    public function test_body_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->body('Hello World');

        $this->assertSame($whatsapp, $result);
    }

    public function test_template_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->template('my_template');

        $this->assertSame($whatsapp, $result);
    }

    public function test_variables_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->variables(['name' => 'John']);

        $this->assertSame($whatsapp, $result);
    }

    public function test_media_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->media('https://example.com/image.jpg');

        $this->assertSame($whatsapp, $result);
    }

    public function test_document_filename_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->documentFilename('report.pdf');

        $this->assertSame($whatsapp, $result);
    }

    public function test_components_method_returns_same_instance_for_chaining(): void
    {
        $whatsapp = Fast2sms::whatsapp();
        $result = $whatsapp->components([['type' => 'body']]);

        $this->assertSame($whatsapp, $result);
    }

    public function test_send_text_returns_whatsapp_response(): void
    {
        Http::fake([
            '*/whatsapp-session' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendText('Hello');

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function test_send_image_returns_whatsapp_response(): void
    {
        Http::fake([
            '*/whatsapp-session' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendImage('https://example.com/image.jpg', 'A caption');

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function test_send_document_returns_whatsapp_response(): void
    {
        Http::fake([
            '*/whatsapp-session' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendDocument('https://example.com/doc.pdf', 'doc.pdf', 'My doc');

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function test_send_interactive_returns_whatsapp_response(): void
    {
        Http::fake([
            '*/whatsapp/*/messages' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendInteractive([
            'type' => 'button',
            'body' => ['text' => 'Pick one'],
            'action' => ['buttons' => []],
        ]);

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function test_send_location_returns_whatsapp_response(): void
    {
        Http::fake([
            '*/whatsapp/*/messages' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendLocation(28.6139, 77.2090, 'New Delhi', 'India');

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function test_send_reaction_returns_whatsapp_response(): void
    {
        Http::fake([
            '*/whatsapp/*/messages' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendReaction('msg_id_123', '👍');

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    public function test_fluent_chain_to_body_send(): void
    {
        Http::fake([
            '*/whatsapp-session' => Http::response([
                'return' => true,
                'success' => true,
                'message' => 'Sent',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()
            ->to('919999999999')
            ->from('123456')
            ->type(WhatsAppType::TEXT)
            ->body('Hello from chain')
            ->send();

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
    }
}
