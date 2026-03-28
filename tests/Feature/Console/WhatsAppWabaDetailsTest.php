<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature\Console;

use Artisan;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Tests\TestCase;

class WhatsAppWabaDetailsTest extends TestCase
{
    #[Test]
    public function it_fetches_number_details_and_displays_table(): void
    {
        $response = $this->mockResponse(true, [
            [
                'waba_id' => 'waba_123',
                'phone_number_id' => 'pn_456',
                'number' => '9876543210',
                'verified_name' => 'Test Business',
                'connection_status' => 'connected',
            ],
        ]);

        $this->mock(WhatsAppInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('getWabaDetails')->with('number')->once()->andReturn($response);
        });

        $this->artisan('fast2sms:waba', ['type' => 'number'])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_fetches_template_details_and_displays_table(): void
    {
        $response = $this->mockResponse(true, [
            [
                'template_name' => 'welcome_msg',
                'message_id' => 'msg_001',
                'meta_template_id' => 'meta_001',
                'status' => 'approved',
                'category' => 'MARKETING',
            ],
        ]);

        $this->mock(WhatsAppInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('getWabaDetails')->with('template')->once()->andReturn($response);
        });

        $this->artisan('fast2sms:waba', ['type' => 'template'])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_outputs_json_when_json_flag_is_set(): void
    {
        $rawData = ['waba_id' => 'waba_123', 'number' => '9876543210'];
        $response = $this->mockResponse(true, $rawData);

        $this->mock(WhatsAppInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('getWabaDetails')->with('number')->once()->andReturn($response);
        });

        Artisan::call('fast2sms:waba', ['type' => 'number', '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('waba_id', $decoded);
    }

    #[Test]
    public function it_returns_failure_for_invalid_type(): void
    {
        // WhatsAppInterface is injected but handle() returns early before calling it
        $this->mock(WhatsAppInterface::class, function ($mock) {
            $mock->shouldNotReceive('getWabaDetails');
        });

        $this->artisan('fast2sms:waba', ['type' => 'invalid'])
            ->assertExitCode(1);
    }

    #[Test]
    public function it_returns_failure_when_api_call_fails(): void
    {
        $response = $this->mockResponse(false, [], 'Unauthorized');

        $this->mock(WhatsAppInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('getWabaDetails')->with('number')->once()->andReturn($response);
        });

        $this->artisan('fast2sms:waba', ['type' => 'number'])
            ->assertExitCode(1);
    }

    #[Test]
    public function it_outputs_json_error_on_api_failure_with_json_flag(): void
    {
        $response = $this->mockResponse(false, [], 'Unauthorized');

        $this->mock(WhatsAppInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('getWabaDetails')->with('number')->once()->andReturn($response);
        });

        Artisan::call('fast2sms:waba', ['type' => 'number', '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        $this->assertArrayHasKey('error', $decoded);
    }

    #[Test]
    public function it_handles_empty_data_gracefully(): void
    {
        $response = $this->mockResponse(true, []);

        $this->mock(WhatsAppInterface::class, function ($mock) use ($response) {
            $mock->shouldReceive('getWabaDetails')->with('number')->once()->andReturn($response);
        });

        $this->artisan('fast2sms:waba', ['type' => 'number'])
            ->assertExitCode(0);
    }

    /**
     * Helper to build a mock WhatsAppResponse with controlled return values.
     *
     * @param array<int|string, mixed> $rawData
     */
    private function mockResponse(bool $success, array $rawData, string $message = 'OK'): MockInterface
    {
        $mock = Mockery::mock(WhatsAppResponse::class);
        $mock->shouldReceive('isSuccess')->andReturn($success);
        $mock->shouldReceive('getRawData')->andReturn($rawData);
        $mock->message = $message;

        return $mock;
    }
}
