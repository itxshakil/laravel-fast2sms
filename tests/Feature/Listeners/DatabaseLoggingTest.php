<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature\Listeners;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Listeners\LogSmsFailed;
use Shakil\Fast2sms\Listeners\LogSmsSent;
use Shakil\Fast2sms\Models\Fast2smsLog;
use Shakil\Fast2sms\Responses\SmsResponse;
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
