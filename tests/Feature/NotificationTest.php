<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;
use Shakil\Fast2sms\Notifications\Messages\WhatsAppMessage;
use Shakil\Fast2sms\Tests\TestCase;

class NotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'fast2sms.api_key' => 'test-api-key',
            'fast2sms.whatsapp.default_phone_number_id' => '123456',
        ]);
    }

    #[Test]
    public function it_can_send_sms_notification(): void
    {
        Http::fake([
            '*/bulkV2' => Http::response(['return' => true, 'request_id' => 'sms_req_123'], 200),
        ]);

        $notifiable = new TestNotifiable();
        $notification = new TestSmsNotification();

        $notifiable->notify($notification);

        Http::assertSent(function ($request) {
            $data = $this->getMultipartPayload($request);

            return $data['numbers'] === '9999999999'
                && $data['message'] === 'Test SMS Notification'
                && $data['route'] === 'q';
        });
    }

    #[Test]
    public function it_can_send_whatsapp_notification(): void
    {
        Http::fake([
            '*/whatsapp-session' => Http::response(['success' => true, 'message' => 'WhatsApp sent'], 200),
        ]);

        $notifiable = new TestNotifiable();
        $notification = new TestWhatsAppNotification();

        $notifiable->notify($notification);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.fast2sms.com/dev/whatsapp-session'
                && $request['to'] === '919876543210'
                && $request['type'] === 'text'
                && $request['text']['body'] === 'Test WhatsApp Notification';
        });
    }

    #[Test]
    public function it_can_send_whatsapp_template_notification(): void
    {
        Http::fake([
            '*/whatsapp?*' => Http::response(['status' => true, 'message' => 'Template sent'], 200),
        ]);

        $notifiable = new TestNotifiable();
        $notification = new TestWhatsAppTemplateNotification();

        $notifiable->notify($notification);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/whatsapp')
                && $request['numbers'] === '919876543210'
                && $request['message_id'] === 'WELCOME_01';
        });
    }

    private function getMultipartPayload($request)
    {
        $payload = [];
        foreach ($request->data() as $part) {
            $payload[$part['name']] = $part['contents'];
        }

        return $payload;
    }
}

class TestNotifiable
{
    use \Illuminate\Notifications\Notifiable;

    public function routeNotificationForSms()
    {
        return '9999999999';
    }

    public function routeNotificationForWhatsApp()
    {
        return '919876543210';
    }

    public function getKey()
    {
        return 1;
    }
}

class TestSmsNotification extends Notification
{
    public function via($notifiable)
    {
        return ['fast2sms'];
    }

    public function toSms($notifiable)
    {
        return (new SmsMessage('Test SMS Notification'))->route(SmsRoute::QUICK);
    }
}

class TestWhatsAppNotification extends Notification
{
    public function via($notifiable)
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable)
    {
        return new WhatsAppMessage('Test WhatsApp Notification');
    }
}

class TestWhatsAppTemplateNotification extends Notification
{
    public function via($notifiable)
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable)
    {
        return (new WhatsAppMessage())->template('WELCOME_01');
    }
}
