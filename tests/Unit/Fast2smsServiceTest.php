<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit;

use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Contracts\ClientInterface;
use Shakil\Fast2sms\DataTransferObjects\Fast2smsConfig;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Tests\TestCase;

class Fast2smsServiceTest extends TestCase
{
    #[Test]
    public function it_can_set_flash_message(): void
    {
        $fast2sms = $this->createFast2sms();
        $fast2sms->flash(true);
        $this->assertTrue($fast2sms->isFlash());

        $fast2sms->flash(false);
        $this->assertFalse($fast2sms->isFlash());
    }

    #[Test]
    public function it_can_set_schedule_time_from_datetime(): void
    {
        $fast2sms = $this->createFast2sms();
        $date = new DateTimeImmutable('2026-01-01 10:00:00');
        $fast2sms->schedule($date);

        $this->assertEquals('2026-01-01-10-00', $fast2sms->getScheduleTime());
    }

    #[Test]
    public function it_throws_exception_for_invalid_schedule_string_format(): void
    {
        $fast2sms = $this->createFast2sms();

        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Invalid schedule time format. Expected YYYY-MM-DD-HH-MM.');

        $fast2sms->schedule('2026/01/01 10:00');
    }

    #[Test]
    public function it_resets_parameters_after_api_call(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('post')->once()->andReturn(new \Shakil\Fast2sms\Responses\Fast2smsResponse(['return' => true, 'request_id' => '123']));

        $fast2sms = new Fast2sms($client, Fast2smsConfig::fromArray(['api_key' => 'test_key', 'default_sender_id' => 'FSTSMS', 'default_route' => 'q']));
        $fast2sms->to('9999999999')
            ->message('Test')
            ->flash()
            ->send();

        $this->assertEmpty($fast2sms->getNumbers());
        $this->assertNull($fast2sms->getScheduleTime());
        $this->assertFalse($fast2sms->isFlash());
    }

    #[Test]
    public function it_throws_exception_if_entity_id_missing_for_dlt_manual(): void
    {
        $fast2sms = $this->createFast2sms();

        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Entity ID is required for DLT.');

        $fast2sms->to('9999999999')
            ->route(SmsRoute::DLT_MANUAL)
            ->templateId('TPL123')
            ->senderId('FSTSMS')
            ->variables(['var'])
            ->send();
    }

    #[Test]
    public function it_can_be_instantiated_without_api_key_if_driver_is_log(): void
    {
        $fast2sms = $this->createFast2sms(['api_key' => '', 'driver' => 'log']);
        $this->assertInstanceOf(Fast2sms::class, $fast2sms);
    }

    #[Test]
    public function it_dispatches_low_balance_event_when_threshold_is_hit(): void
    {
        \Illuminate\Support\Facades\Event::fake();

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('post')->with('/wallet', [])->andReturn(new \Shakil\Fast2sms\Responses\WalletBalanceResponse(['return' => true, 'wallet' => 100, 'sms_count' => 200]));

        $fast2sms = new Fast2sms($client, Fast2smsConfig::fromArray(['api_key' => 'test_key', 'default_sender_id' => 'FSTSMS', 'default_route' => 'q']));
        $fast2sms->checkBalance(500);

        \Illuminate\Support\Facades\Event::assertDispatched(\Shakil\Fast2sms\Events\LowBalanceDetected::class, function ($event) {
            return $event->balance === 100.0 && $event->threshold === 500.0;
        });
    }

    #[Test]
    public function monitor_command_handles_balance_and_dispatches_event(): void
    {
        \Illuminate\Support\Facades\Event::fake();

        $command = new \Shakil\Fast2sms\Console\Commands\MonitorSmsBalance();

        // Mock the output to avoid errors when calling $this->info() etc.
        $output = new \Symfony\Component\Console\Output\NullOutput();
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $command->setOutput(new \Illuminate\Console\OutputStyle($input, $output));

        $command->handleBalance(100.0, 500.0);

        \Illuminate\Support\Facades\Event::assertDispatched(\Shakil\Fast2sms\Events\LowBalanceDetected::class);
    }

    private function createFast2sms(array $config = []): Fast2sms
    {
        $client = Mockery::mock(ClientInterface::class);
        $config = array_merge([
            'api_key' => 'test_key',
            'default_sender_id' => 'FSTSMS',
            'default_route' => 'q',
        ], $config);

        return new Fast2sms($client, Fast2smsConfig::fromArray($config));
    }
}
