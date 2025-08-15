<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Channels;

use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Channels\SmsChannel;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;
use Shakil\Fast2sms\Tests\TestCase;

#[CoversClass(SmsChannel::class)]
class SmsChannelTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Fast2sms::fake();
    }

    /**
     * Test sending a simple string message.
     *
     * @throws Fast2smsException
     */
    #[Test]
    public function it_can_send_string_message(): void
    {
        $channel = new SmsChannel;
        $notifiable = new TestNotifiable;
        $notification = new TestStringNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertSent(function ($parameters) {
            return $parameters['numbers'] === '1234567890' &&
                $parameters['message'] === 'Test message' &&
                $parameters['route'] === SmsRoute::QUICK->value;
        });
    }

    /**
     * Test sending a DLT message with template.
     *
     * @throws Fast2smsException
     */
    #[Test]
    public function it_can_send_dlt_message(): void
    {
        $channel = new SmsChannel;
        $notifiable = new TestNotifiable;
        $notification = new TestDltNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertSent(function ($parameters) {
            return $parameters['numbers'] === '1234567890' &&
                $parameters['template_id'] === 'template123' &&
                $parameters['variables_values'] === 'var1|var2' &&
                $parameters['sender_id'] === 'TESTID' &&
                $parameters['route'] === SmsRoute::DLT->value;
        });
    }

    /**
     * Test sending a message with language specification.
     *
     * @throws Fast2smsException
     */
    #[Test]
    public function it_can_send_quick_message_with_language(): void
    {
        $channel = new SmsChannel;
        $notifiable = new TestNotifiable;
        $notification = new TestLanguageNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertSent(function ($parameters) {
            return $parameters['numbers'] === '1234567890' &&
                $parameters['message'] === 'Test unicode message' &&
                $parameters['language'] === SmsLanguage::UNICODE->value &&
                $parameters['route'] === SmsRoute::QUICK->value;
        });
    }

    /**
     * Test that message is not sent when phone number is missing.
     */
    #[Test]
    public function it_does_not_send_when_phone_number_is_missing(): void
    {
        $channel = new SmsChannel;
        $notifiable = new TestNotifiableWithoutPhone;
        $notification = new TestStringNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertNotSent();
    }

    /**
     * Test using default route when not specified.
     *
     * @throws Fast2smsException
     */
    #[Test]
    public function it_sends_with_default_route_when_not_specified(): void
    {
        config(['fast2sms.default_route' => SmsRoute::QUICK->value]);

        $channel = new SmsChannel;
        $notifiable = new TestNotifiable;
        $notification = new TestDefaultRouteNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertSent(function ($parameters) {
            return $parameters['route'] === SmsRoute::QUICK->value;
        });
    }

    /**
     * Test using default sender ID when not specified.
     *
     * @throws Fast2smsException
     */
    #[Test]
    public function it_sends_with_default_sender_id_when_not_specified(): void
    {
        config(['fast2sms.default_sender_id' => 'DEFAULT']);

        $channel = new SmsChannel;
        $notifiable = new TestNotifiable;
        $notification = new TestDefaultSenderNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertSent(function ($parameters) {
            return $parameters['sender_id'] === 'DEFAULT';
        });
    }
}

/**
 * Test notifiable class with phone number.
 */
class TestNotifiable
{
    public function routeNotificationFor(string $channel, ?Notification $notification = null): string
    {
        return '1234567890';
    }
}

/**
 * Test notifiable class without phone number.
 */
class TestNotifiableWithoutPhone
{
    public function routeNotificationFor(string $channel, ?Notification $notification = null): ?string
    {
        return null;
    }
}

/**
 * Test notification class for string messages.
 */
class TestStringNotification extends Notification
{
    public function toSms(mixed $notifiable): string
    {
        return 'Test message';
    }
}

/**
 * Test notification class for DLT messages.
 */
class TestDltNotification extends Notification
{
    public function toSms(mixed $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->route(SmsRoute::DLT)
            ->template('template123', ['var1', 'var2'])
            ->from('TESTID');
    }
}

/**
 * Test notification class for messages with language specification.
 */
class TestLanguageNotification extends Notification
{
    public function toSms(mixed $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->content('Test unicode message')
            ->route(SmsRoute::QUICK)
            ->language(SmsLanguage::UNICODE);
    }
}

/**
 * Test notification class for messages without specified route.
 */
class TestDefaultRouteNotification extends Notification
{
    public function toSms(mixed $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->content('Test message');
    }
}

/**
 * Test notification class for messages with default sender ID.
 */
class TestDefaultSenderNotification extends Notification
{
    public function toSms(mixed $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->content('Test message')
            ->route(SmsRoute::DLT)
            ->template('template123', ['var1']);
    }
}
