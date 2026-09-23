<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Override;
use Shakil\Fast2sms\Events\MessageDelivered;
use Shakil\Fast2sms\Events\MessageFailed;
use Shakil\Fast2sms\Models\Fast2smsLog;
use Shakil\Fast2sms\Tests\TestCase;

final class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private const string SECRET = 'super-secret-token';

    private const string URL = '/fast2sms/webhook/' . self::SECRET;

    public function test_it_accepts_a_valid_webhook_and_dispatches_delivered_event(): void
    {
        Event::fake([MessageDelivered::class, MessageFailed::class]);

        $this->postJson(self::URL, $this->deliveredPayload())
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'delivered', 'request_id' => 'req_123']);

        Event::assertDispatched(MessageDelivered::class);
        Event::assertNotDispatched(MessageFailed::class);
    }

    public function test_it_dispatches_failed_event_for_failed_status(): void
    {
        Event::fake([MessageDelivered::class, MessageFailed::class]);

        $this->postJson(self::URL, $this->deliveredPayload([
            'status' => 'failed',
            'failure_reason' => 'DND number',
        ]))->assertOk();

        Event::assertDispatched(MessageFailed::class);
        Event::assertNotDispatched(MessageDelivered::class);
    }

    public function test_it_rejects_an_invalid_secret(): void
    {
        $this->postJson('/fast2sms/webhook/wrong-secret', $this->deliveredPayload())
            ->assertForbidden();
    }

    public function test_it_reconciles_an_existing_log_row(): void
    {
        Fast2smsLog::query()->create([
            'request_id' => 'req_123',
            'payload' => ['numbers' => '9999999999'],
            'is_success' => true,
        ]);

        $this->postJson(self::URL, $this->deliveredPayload())->assertOk();

        $log = Fast2smsLog::query()->where('request_id', 'req_123')->firstOrFail();
        $this->assertSame('delivered', $log->getAttribute('status'));
        $this->assertSame('0.2000', $log->getAttribute('amount_debited'));
        $this->assertTrue((bool) $log->getAttribute('is_success'));
        $this->assertSame(1, Fast2smsLog::query()->count());
    }

    public function test_it_creates_a_log_row_when_none_exists(): void
    {
        $this->postJson(self::URL, $this->deliveredPayload())->assertOk();

        $this->assertDatabaseHas('fast2sms_logs', [
            'request_id' => 'req_123',
            'status' => 'delivered',
        ]);
    }

    public function test_it_ignores_a_stale_retry(): void
    {
        Fast2smsLog::query()->create([
            'request_id' => 'req_123',
            'status' => 'delivered',
            'is_success' => true,
            'post_attempt' => 3,
            'delivery_timestamp' => 1718712002,
        ]);

        // An older retry (lower post_attempt) with a failed status must not overwrite.
        $this->postJson(self::URL, $this->deliveredPayload([
            'status' => 'failed',
            'post_attempt' => 1,
        ]))->assertOk();

        $log = Fast2smsLog::query()->where('request_id', 'req_123')->firstOrFail();
        $this->assertSame('delivered', $log->getAttribute('status'));
        $this->assertTrue((bool) $log->getAttribute('is_success'));
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('fast2sms.database_logging', true);
        $app['config']->set('fast2sms.webhook.enabled', true);
        $app['config']->set('fast2sms.webhook.auto_route', true);
        $app['config']->set('fast2sms.webhook.path', 'fast2sms/webhook');
        $app['config']->set('fast2sms.webhook.secret', self::SECRET);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param  array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function deliveredPayload(array $overrides = []): array
    {
        return array_merge([
            'request_id' => 'req_123',
            'status' => 'delivered',
            'mobile' => '9999999999',
            'amount_debited' => '0.2000',
            'delivery_timestamp' => 1718712002,
            'post_attempt' => 1,
            'channel' => 'sms',
        ], $overrides);
    }
}
