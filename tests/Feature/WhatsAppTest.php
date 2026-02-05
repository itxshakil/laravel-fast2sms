<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Jobs\SendWhatsAppJob;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Tests\TestCase;

class WhatsAppTest extends TestCase
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

    #[Test]
    public function it_can_send_a_session_message(): void
    {
        Http::fake([
            '*/whatsapp-session' => Http::response([
                'success' => true,
                'message' => 'Message sent successfully',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendSessionMessage('919876543210', [
            'type' => 'text',
            'text' => ['body' => 'Hello World'],
        ]);

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('Message sent successfully', $response->message);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.fast2sms.com/dev/whatsapp-session'
                && $request['to'] === '919876543210'
                && $request['phone_number_id'] === '123456'
                && $request['type'] === 'text'
                && $request['text']['body'] === 'Hello World';
        });
    }

    #[Test]
    public function it_can_send_a_meta_format_message(): void
    {
        Http::fake([
            '*/whatsapp/v24.0/123456/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '919876543210', 'wa_id' => '919876543210']],
                'messages' => [['id' => 'wamid.HBgLOTExOTg3NjU0MzIxMBIDABEYEjk1NzlBQjY0MEE0RTRCQTlBQQA=']],
                'success' => true,
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendMetaMessage('919876543210', [
            'messaging_product' => 'whatsapp',
            'type' => 'text',
            'text' => ['body' => 'Hello from Meta API'],
        ]);

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.fast2sms.com/dev/whatsapp/v24.0/123456/messages'
                && $request['to'] === '919876543210'
                && $request['messaging_product'] === 'whatsapp'
                && $request['type'] === 'text';
        });
    }

    #[Test]
    public function it_can_send_a_template_message(): void
    {
        Http::fake([
            '*/whatsapp?*' => Http::response([
                'status' => true,
                'message' => 'Message sent successfully',
                'request_id' => '6a3b2c1d',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->sendTemplateMessage('919876543210', 12345, ['John', '1234']);

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/whatsapp')
                && $request['numbers'] === '919876543210'
                && $request['message_id'] === 12345
                && $request['variables_values'] === 'John|1234';
        });
    }

    #[Test]
    public function it_can_manage_templates(): void
    {
        Http::fake([
            '*/message_templates' => Http::response([
                'success' => true,
                'id' => '1063801391294860',
                'status' => 'PENDING',
                'category' => 'MARKETING',
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->manageTemplates('POST', null, [
            'name' => 'welcome_message',
            'category' => 'MARKETING',
            'language' => 'en_US',
            'components' => [['type' => 'BODY', 'text' => 'Hello {{1}}']],
        ]);

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/message_templates')
                && $request->method() === 'POST'
                && $request['name'] === 'welcome_message';
        });
    }

    #[Test]
    public function it_can_get_waba_details(): void
    {
        Http::fake([
            '*/dlt_manager/whatsapp?*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'waba_id' => '654321',
                        'phone_number_id' => '123456',
                        'number' => '919876543210',
                    ],
                ],
            ], 200),
        ]);

        $response = Fast2sms::whatsapp()->getWabaDetails('number');

        $this->assertTrue($response->isSuccess());
        $this->assertIsArray($response->data);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/dlt_manager/whatsapp')
                && $request['type'] === 'number';
        });
    }

    #[Test]
    public function it_supports_fluent_whatsapp_interface(): void
    {
        Fast2sms::fake();

        $response = Fast2sms::viaWhatsApp()
            ->to('919876543210')
            ->sendText('Fluent Hello');

        $this->assertTrue($response->isSuccess());

        Fast2sms::assertSent(function ($payload) {
            return $payload['to'] === '919876543210'
                && $payload['type'] === 'text'
                && $payload['text']['body'] === 'Fluent Hello';
        });

        Fast2sms::viaWhatsApp()
            ->to('919876543210')
            ->template('WELCOME_01')
            ->variables(['John'])
            ->send();

        Fast2sms::assertSent(function ($payload) {
            return ($payload['numbers'] ?? null) === '919876543210'
                && ($payload['message_id'] ?? null) === 'WELCOME_01'
                && ($payload['variables_values'] ?? null) === 'John';
        });
    }

    #[Test]
    public function it_can_send_advanced_meta_template_with_components(): void
    {
        Http::fake([
            '*/messages' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => 'John'],
                ],
            ],
        ];

        $response = Fast2sms::viaWhatsApp('919876543210')
            ->template('welcome_msg')
            ->components($components)
            ->send();

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return $request['template']['name'] === 'welcome_msg'
                && $request['template']['components'][0]['parameters'][0]['text'] === 'John';
        });
    }

    #[Test]
    public function it_can_send_location_message(): void
    {
        Http::fake([
            '*/messages' => Http::response(['success' => true], 200),
        ]);

        $response = Fast2sms::viaWhatsApp('919876543210')
            ->sendLocation(12.9716, 77.5946, 'Bangalore', 'Karnataka, India');

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return $request['type'] === 'location'
                && $request['location']['latitude'] === 12.9716
                && $request['location']['name'] === 'Bangalore';
        });
    }

    #[Test]
    public function it_can_send_reaction(): void
    {
        Http::fake([
            '*/messages' => Http::response(['success' => true], 200),
        ]);

        $response = Fast2sms::viaWhatsApp('919876543210')
            ->sendReaction('wamid.XYZ', '👍');

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return $request['type'] === 'reaction'
                && $request['reaction']['message_id'] === 'wamid.XYZ'
                && $request['reaction']['emoji'] === '👍';
        });
    }

    #[Test]
    public function it_can_block_users(): void
    {
        Http::fake([
            '*/block_users' => Http::response(['success' => true], 200),
        ]);

        $response = Fast2sms::viaWhatsApp()->block('919876543210');

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/block_users')
                && $request['block_users'][0]['input'] === '919876543210';
        });
    }

    #[Test]
    public function it_can_get_delivery_report(): void
    {
        Http::fake([
            '*/whatsapp/REQ123' => Http::response([
                'success' => true,
                'data' => [['status' => 'delivered']],
            ], 200),
        ]);

        $response = Fast2sms::viaWhatsApp()->getDeliveryReport('REQ123');

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('delivered', $response->data[0]['status']);
    }

    #[Test]
    public function it_can_upload_media(): void
    {
        Http::fake([
            '*/media' => Http::response(['id' => 'MEDIA_ID', 'success' => true], 200),
        ]);

        // Create a dummy file
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'fake media content');

        $response = Fast2sms::viaWhatsApp()->uploadMedia($tempFile, 'image/jpeg');

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('MEDIA_ID', $response->id);

        unlink($tempFile);
    }

    #[Test]
    public function it_queues_advanced_messages(): void
    {
        Queue::fake();

        $components = [['type' => 'body', 'parameters' => [['type' => 'text', 'text' => 'John']]]];

        Fast2sms::viaWhatsApp('919876543210')
            ->template('welcome')
            ->components($components)
            ->queue();

        Queue::assertPushed(SendWhatsAppJob::class, function ($job) use ($components) {
            return $job->parameters->to === '919876543210'
                && $job->parameters->templateId === 'welcome'
                && $job->parameters->components === $components;
        });
    }

    #[Test]
    public function it_can_queue_whatsapp_messages(): void
    {
        Queue::fake();

        Fast2sms::viaWhatsApp('919876543210')
            ->template('WELCOME_01')
            ->variables(['John'])
            ->onQueue('custom-queue')
            ->delay(60)
            ->queue();

        Queue::assertPushed(SendWhatsAppJob::class, function ($job) {
            return $job->parameters->to === '919876543210'
                && $job->parameters->templateId === 'WELCOME_01'
                && $job->parameters->variables === ['John']
                && $job->queue === 'custom-queue'
                && $job->delay === 60;
        });
    }

    #[Test]
    public function it_can_send_session_messages_using_fluent_interface(): void
    {
        Fast2sms::fake();

        Fast2sms::viaWhatsApp('919876543210')
            ->type(WhatsAppType::TEXT)
            ->body('Fluent Session Text')
            ->send();

        Fast2sms::assertSent(function ($payload) {
            return $payload['to'] === '919876543210'
                && $payload['type'] === 'text'
                && $payload['text']['body'] === 'Fluent Session Text';
        });

        Fast2sms::viaWhatsApp('919876543210')
            ->type(WhatsAppType::IMAGE)
            ->media('https://example.com/image.jpg')
            ->body('Check this out')
            ->send();

        Fast2sms::assertSent(function ($payload) {
            return $payload['to'] === '919876543210'
                && $payload['type'] === 'image'
                && $payload['image']['link'] === 'https://example.com/image.jpg'
                && $payload['image']['caption'] === 'Check this out';
        });
    }
}
