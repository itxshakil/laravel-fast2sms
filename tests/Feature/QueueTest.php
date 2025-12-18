<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Jobs\SendSmsJob;
use Shakil\Fast2sms\Tests\TestCase;

class QueueTest extends TestCase
{
    protected Fast2sms $fast2sms;

    /**
     * Setup the test environment.
     *
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->fast2sms = $this->app->make('fast2sms');
    }

    #[Test]
    public function it_can_queue_quick_sms(): void
    {
        $this->fast2sms->quickQueue('1234567890', 'Test message');

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->parameters->numbers === ['1234567890']
                && $job->parameters->message === 'Test message'
                && $job->parameters->route === SmsRoute::QUICK;
        });
    }

    /**
     * @throws Fast2smsException
     */
    #[Test]
    public function it_can_queue_dlt_sms(): void
    {
        $this->withoutExceptionHandling();

        $this->fast2sms->dltQueue(
            '1234567890',
            'template123',
            ['var1', 'var2'],
            'SENDER1',
        );

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->parameters->numbers === ['1234567890']
                && $job->parameters->templateId === 'template123'
                && $job->parameters->variablesValues === 'var1|var2'
                && $job->parameters->senderId === 'SENDER1'
                && $job->parameters->route === SmsRoute::DLT;
        });
    }

    #[Test]
    public function it_can_queue_otp_sms(): void
    {
        $this->fast2sms->otpQueue('1234567890', '123456');

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->parameters->numbers === ['1234567890']
                && $job->parameters->message === '123456'
                && $job->parameters->route === SmsRoute::OTP;
        });
    }

    /**
     * @throws Fast2smsException
     */
    #[Test]
    public function it_can_queue_with_custom_connection(): void
    {
        $this->withoutExceptionHandling();
        $this->fast2sms
            ->to('1234567890')
            ->message('Test')
            ->route(SmsRoute::QUICK)
            ->onConnection('redis')
            ->queue();

        Queue::assertPushed(SendSmsJob::class, function ($job, $queue) {
            return $job->connection === 'redis' && $queue === null;
        });
    }

    #[Test]
    public function it_can_queue_with_custom_queue_name(): void
    {
        $this->fast2sms
            ->to('1234567890')
            ->message('Test')
            ->route(SmsRoute::QUICK)
            ->onQueue('sms')
            ->queue();

        Queue::assertPushedOn('sms', SendSmsJob::class);
    }

    #[Test]
    public function it_can_queue_with_delay(): void
    {
        $this->fast2sms
            ->to('1234567890')
            ->message('Test')
            ->route(SmsRoute::QUICK)
            ->delay(60)
            ->queue();

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->delay === 60;
        });
    }

    #[Test]
    public function it_resets_queue_config_after_queuing(): void
    {
        // First message with custom queue config
        $this->fast2sms
            ->to('1234567890')
            ->message('Test')
            ->route(SmsRoute::QUICK)
            ->onConnection('redis')
            ->onQueue('sms')
            ->delay(60)
            ->queue();

        Queue::assertPushedOn('sms', SendSmsJob::class);

        // Second message without queue config
        $this->fast2sms
            ->to('0987654321')
            ->message('Test 2')
            ->route(SmsRoute::QUICK)
            ->queue();

        // Assert both jobs were pushed
        Queue::assertPushed(SendSmsJob::class, 2);

        // Check the second job was pushed to the default queue
        Queue::assertPushed(SendSmsJob::class, function ($job, $queue) {
            return $queue === null && $job->parameters->numbers === ['0987654321'];
        });
    }
}
