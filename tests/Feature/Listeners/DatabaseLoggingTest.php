<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature\Listeners;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Listeners\LogSmsFailed;
use Shakil\Fast2sms\Listeners\LogSmsSent;
use Shakil\Fast2sms\Listeners\LogWhatsAppFailed;
use Shakil\Fast2sms\Listeners\LogWhatsAppSent;
use Shakil\Fast2sms\Models\Fast2smsLog;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Tests\TestCase;

class DatabaseLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_successful_sms_to_database(): void
    {
        $payload = ['numbers' => '9999999999', 'message' => 'Test message'];
        $response = new SmsResponse([
            'return' => true,
            'request_id' => 'req_123',
            'message' => 'Success',
        ]);

        $event = new SmsSent($payload, $response);
        (new LogSmsSent())->handle($event);

        $this->assertDatabaseHas('fast2sms_logs', [
            'request_id' => 'req_123',
            'is_success' => true,
        ]);

        $log = Fast2smsLog::first();
        $this->assertEquals($payload, $log->payload);
        $this->assertEquals($response->json(), $log->response);
    }

    public function test_it_logs_failed_sms_to_database(): void
    {
        $payload = ['numbers' => '9999999999', 'message' => 'Test message'];
        $exception = new Fast2smsException('API Error');
        $apiResponse = ['return' => false, 'message' => 'API Error'];

        $event = new SmsFailed($payload, $exception, $apiResponse);
        (new LogSmsFailed())->handle($event);

        $this->assertDatabaseHas('fast2sms_logs', [
            'is_success' => false,
            'error_message' => 'API Error',
        ]);

        $log = Fast2smsLog::first();
        $this->assertEquals($payload, $log->payload);
        $this->assertEquals($apiResponse, $log->response);
    }

    public function test_it_does_not_log_when_disabled(): void
    {
        config(['fast2sms.database_logging' => false]);

        $payload = ['numbers' => '9999999999', 'message' => 'Test message'];
        $response = new SmsResponse(['return' => true, 'request_id' => 'req_123']);

        $event = new SmsSent($payload, $response);
        (new LogSmsSent())->handle($event);

        $this->assertDatabaseEmpty('fast2sms_logs');
    }

    public function test_it_logs_successful_whatsapp_to_database(): void
    {
        $payload = ['to' => '919999999999', 'type' => 'text', 'body' => 'Hello'];
        $response = new WhatsAppResponse([
            'return' => true,
            'request_id' => 'wa_req_456',
            'message' => 'WhatsApp sent',
        ]);

        $event = new WhatsAppSent($payload, $response);
        (new LogWhatsAppSent())->handle($event);

        $this->assertDatabaseHas('fast2sms_logs', [
            'is_success' => true,
        ]);

        $log = Fast2smsLog::first();
        $this->assertEquals($payload, $log->payload);
        $this->assertEquals($response->getRawData(), $log->response);
    }

    public function test_it_logs_failed_whatsapp_to_database(): void
    {
        $payload = ['to' => '919999999999', 'type' => 'text'];
        $exception = new Fast2smsException('WhatsApp API Error');
        $apiResponse = ['return' => false, 'message' => 'WhatsApp API Error'];

        $event = new WhatsAppFailed($payload, $exception, $apiResponse);
        (new LogWhatsAppFailed())->handle($event);

        $this->assertDatabaseHas('fast2sms_logs', [
            'is_success' => false,
            'error_message' => 'WhatsApp API Error',
        ]);

        $log = Fast2smsLog::first();
        $this->assertEquals($payload, $log->payload);
        $this->assertEquals($apiResponse, $log->response);
    }

    public function test_it_does_not_log_whatsapp_when_disabled(): void
    {
        config(['fast2sms.database_logging' => false]);

        $payload = ['to' => '919999999999', 'type' => 'text'];
        $response = new WhatsAppResponse(['return' => true, 'request_id' => 'wa_req_789']);

        $event = new WhatsAppSent($payload, $response);
        (new LogWhatsAppSent())->handle($event);

        $this->assertDatabaseEmpty('fast2sms_logs');
    }

    public function test_it_logs_failed_whatsapp_without_api_response(): void
    {
        $payload = ['to' => '919999999999', 'type' => 'text'];
        $exception = new Fast2smsException('Connection timeout');

        $event = new WhatsAppFailed($payload, $exception);
        (new LogWhatsAppFailed())->handle($event);

        $this->assertDatabaseHas('fast2sms_logs', [
            'is_success' => false,
            'error_message' => 'Connection timeout',
        ]);

        $log = Fast2smsLog::first();
        $this->assertNull($log->response);
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('fast2sms.database_logging', true);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
