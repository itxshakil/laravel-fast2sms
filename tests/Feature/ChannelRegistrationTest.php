<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Notifications\ChannelManager;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Channels\SmsChannel;
use Shakil\Fast2sms\Channels\WhatsAppChannel;
use Shakil\Fast2sms\Tests\TestCase;

class ChannelRegistrationTest extends TestCase
{
    #[Test]
    public function it_resolves_fast2sms_channel_to_sms_channel(): void
    {
        $manager = $this->app->make(ChannelManager::class);

        $channel = $manager->driver('fast2sms');

        $this->assertInstanceOf(SmsChannel::class, $channel);
    }

    #[Test]
    public function it_resolves_whatsapp_channel_to_whats_app_channel(): void
    {
        $manager = $this->app->make(ChannelManager::class);

        $channel = $manager->driver('whatsapp');

        $this->assertInstanceOf(WhatsAppChannel::class, $channel);
    }
}
