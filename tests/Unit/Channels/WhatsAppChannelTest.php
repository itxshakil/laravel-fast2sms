<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Channels;

use BadMethodCallException;
use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Channels\WhatsAppChannel;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Notifications\Messages\WhatsAppMessage;
use Shakil\Fast2sms\Tests\TestCase;

#[CoversClass(WhatsAppChannel::class)]
class WhatsAppChannelTest extends TestCase
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
     * Test sending a plain string message via WhatsApp.
     */
    #[Test]
    public function it_can_send_string_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppStringNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSentTo('9876543210');
    }

    /**
     * Test sending a typed WhatsAppMessage object.
     */
    #[Test]
    public function it_can_send_whatsapp_message_object(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppMessageNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::TEXT;
        });
    }

    /**
     * Test that send is skipped when phone number is missing.
     */
    #[Test]
    public function it_does_not_send_when_phone_number_is_missing(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiableWithoutPhone;
        $notification = new TestWhatsAppStringNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppNotSent();
    }

    /**
     * Test that BadMethodCallException is thrown when toWhatsApp() is missing.
     */
    #[Test]
    public function it_throws_when_to_whatsapp_method_is_missing(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageMatches('/toWhatsApp/');

        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppNotificationWithoutMethod;

        $channel->send($notifiable, $notification);
    }

    /**
     * Test sending a message with a template.
     */
    #[Test]
    public function it_can_send_template_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppTemplateNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSentTo('9876543210');
    }

    /**
     * Test sending a location message.
     */
    #[Test]
    public function it_can_send_location_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppLocationNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::LOCATION;
        });
    }

    /**
     * Test that the recipient from the message object overrides the notifiable route.
     */
    #[Test]
    public function it_uses_to_from_message_when_set(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppMessageWithCustomToNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '1111111111';
        });
    }

    /**
     * Test sending a reaction message via WhatsApp.
     */
    #[Test]
    public function it_can_send_reaction_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppReactionNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::REACTION;
        });
    }

    /**
     * Test sending an interactive message via WhatsApp.
     */
    #[Test]
    public function it_can_send_interactive_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppInteractiveNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSentTo('9876543210');
    }

    /**
     * Test sending an image media message via WhatsApp.
     */
    #[Test]
    public function it_can_send_image_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppImageNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::IMAGE;
        });
    }

    /**
     * Test sending a video media message via WhatsApp.
     */
    #[Test]
    public function it_can_send_video_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppVideoNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::VIDEO;
        });
    }

    /**
     * Test sending an audio media message via WhatsApp.
     */
    #[Test]
    public function it_can_send_audio_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppAudioNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::AUDIO;
        });
    }

    /**
     * Test sending a document media message via WhatsApp.
     */
    #[Test]
    public function it_can_send_document_message(): void
    {
        $channel = new WhatsAppChannel;
        $notifiable = new TestWhatsAppNotifiable;
        $notification = new TestWhatsAppDocumentNotification;

        $channel->send($notifiable, $notification);

        Fast2sms::assertWhatsAppSent(function ($parameters) {
            return $parameters->to === '9876543210' &&
                $parameters->type === WhatsAppType::DOCUMENT;
        });
    }
}

/**
 * Test notifiable with a WhatsApp phone number.
 */
class TestWhatsAppNotifiable
{
    public function routeNotificationFor(string $channel, ?Notification $notification = null): string
    {
        return '9876543210';
    }
}

/**
 * Test notifiable without a phone number.
 */
class TestWhatsAppNotifiableWithoutPhone
{
    public function routeNotificationFor(string $channel, ?Notification $notification = null): ?string
    {
        return null;
    }
}

/**
 * Notification returning a plain string.
 */
class TestWhatsAppStringNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): string
    {
        return 'Hello via WhatsApp';
    }
}

/**
 * Notification returning a WhatsAppMessage object.
 */
class TestWhatsAppMessageNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return (new WhatsAppMessage('Hello'))
            ->type(WhatsAppType::TEXT);
    }
}

/**
 * Notification missing the toWhatsApp() method.
 */
class TestWhatsAppNotificationWithoutMethod extends Notification {}

/**
 * Notification returning a template WhatsAppMessage.
 */
class TestWhatsAppTemplateNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return (new WhatsAppMessage)
            ->type(WhatsAppType::TEXT)
            ->template('tmpl_001', ['var1']);
    }
}

/**
 * Notification returning a location WhatsAppMessage.
 */
class TestWhatsAppLocationNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::forLocation(28.6139, 77.2090, 'New Delhi', 'India');
    }
}

/**
 * Notification returning a WhatsAppMessage with a custom recipient.
 */
class TestWhatsAppMessageWithCustomToNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return (new WhatsAppMessage('Custom recipient'))
            ->to('1111111111')
            ->type(WhatsAppType::TEXT);
    }
}

/**
 * Notification returning a reaction WhatsAppMessage.
 */
class TestWhatsAppReactionNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::forReaction('msg_abc', '👍');
    }
}

/**
 * Notification returning an interactive WhatsAppMessage.
 */
class TestWhatsAppInteractiveNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::forInteractive(['type' => 'button', 'body' => ['text' => 'Pick one']]);
    }
}

/**
 * Notification returning an image WhatsAppMessage.
 */
class TestWhatsAppImageNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::image('https://example.com/image.jpg', 'A caption');
    }
}

/**
 * Notification returning a video WhatsAppMessage.
 */
class TestWhatsAppVideoNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::forVideo('https://example.com/video.mp4');
    }
}

/**
 * Notification returning an audio WhatsAppMessage.
 */
class TestWhatsAppAudioNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::forAudio('https://example.com/audio.mp3');
    }
}

/**
 * Notification returning a document WhatsAppMessage.
 */
class TestWhatsAppDocumentNotification extends Notification
{
    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::document('https://example.com/file.pdf', 'file.pdf');
    }
}
