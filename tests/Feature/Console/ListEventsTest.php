<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature\Console;

use Artisan;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Tests\TestCase;

class ListEventsTest extends TestCase
{
    #[Test]
    public function it_lists_all_package_events_as_table(): void
    {
        $this->artisan('fast2sms:events')
            ->assertExitCode(0);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_outputs_json_when_json_flag_is_set(): void
    {
        $this->artisan('fast2sms:events', ['--json' => true])
            ->assertExitCode(0);

        Artisan::call('fast2sms:events', ['--json' => true]);
        $json = Artisan::output();
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey(SmsSent::class, $decoded);
        $this->assertArrayHasKey(SmsFailed::class, $decoded);
        $this->assertArrayHasKey(WhatsAppSent::class, $decoded);
        $this->assertArrayHasKey(WhatsAppFailed::class, $decoded);
        $this->assertArrayHasKey(LowBalanceDetected::class, $decoded);
    }

    #[Test]
    public function it_outputs_valid_json_with_json_flag(): void
    {
        $output = $this->artisan('fast2sms:events', ['--json' => true])
            ->assertExitCode(0);

        // Run again capturing output
        $result = Artisan::call('fast2sms:events', ['--json' => true]);
        $json = Artisan::output();

        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey(SmsSent::class, $decoded);
        $this->assertArrayHasKey(LowBalanceDetected::class, $decoded);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_lists_five_events(): void
    {
        Artisan::call('fast2sms:events', ['--json' => true]);
        $json = Artisan::output();

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(5, $decoded);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_includes_event_descriptions_in_json_output(): void
    {
        Artisan::call('fast2sms:events', ['--json' => true]);
        $json = Artisan::output();

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertNotEmpty($decoded[SmsSent::class]);
        $this->assertNotEmpty($decoded[LowBalanceDetected::class]);
    }
}
