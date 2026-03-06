<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature\Console;

use Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Tests\TestCase;

class MonitorSmsBalanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['fast2sms.api_key' => 'test-api-key']);
    }

    #[Test]
    public function it_outputs_balance_when_above_threshold(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response([
                'return' => true,
                'wallet' => '1500.00',
                'sms_count' => 5000,
            ]),
        ]);

        $this->artisan('fast2sms:balance', ['--threshold' => 1000])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_dispatches_low_balance_event_when_below_threshold(): void
    {
        Event::fake();

        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response([
                'return' => true,
                'wallet' => '500.00',
                'sms_count' => 100,
            ]),
        ]);

        $this->artisan('fast2sms:balance', ['--threshold' => 1000])
            ->assertExitCode(1);

        Event::assertDispatched(LowBalanceDetected::class, function (LowBalanceDetected $event) {
            return $event->balance === 500.0 && $event->threshold === 1000.0;
        });
    }

    #[Test]
    public function it_outputs_json_when_json_flag_is_set(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response([
                'return' => true,
                'wallet' => '2000.00',
                'sms_count' => 8000,
            ]),
        ]);

        $this->artisan('fast2sms:balance', ['--threshold' => 1000, '--json' => true])
            ->assertExitCode(0);

        Artisan::call('fast2sms:balance', ['--threshold' => 1000, '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        $this->assertArrayHasKey('balance', $decoded);
        $this->assertArrayHasKey('threshold', $decoded);
        $this->assertArrayHasKey('below_threshold', $decoded);
    }

    #[Test]
    public function it_outputs_json_error_on_api_failure_with_json_flag(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response(['return' => false, 'message' => 'Unauthorized'], 401),
        ]);

        $this->artisan('fast2sms:balance', ['--json' => true])
            ->expectsOutputToContain('"error"')
            ->assertExitCode(1);
    }

    #[Test]
    public function it_uses_config_threshold_when_option_not_provided(): void
    {
        config(['fast2sms.balance_threshold' => 500]);

        Event::fake();

        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response([
                'return' => true,
                'wallet' => '300.00',
                'sms_count' => 50,
            ]),
        ]);

        $this->artisan('fast2sms:balance')
            ->assertExitCode(1);

        Event::assertDispatched(LowBalanceDetected::class);
    }

    #[Test]
    public function it_outputs_below_threshold_json_flag(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response([
                'return' => true,
                'wallet' => '200.00',
                'sms_count' => 50,
            ]),
        ]);

        Artisan::call('fast2sms:balance', ['--threshold' => 1000, '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        $this->assertTrue($decoded['below_threshold']);
        $this->assertEquals(200.0, $decoded['balance']);
    }
}
