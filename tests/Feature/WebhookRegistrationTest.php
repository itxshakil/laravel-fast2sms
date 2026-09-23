<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Contracts\WebhookHandlerInterface;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Tests\TestCase;
use Shakil\Fast2sms\Webhooks\WebhookHandler;

/**
 * Covers the opt-in wiring of the webhook receiver under default configuration.
 */
final class WebhookRegistrationTest extends TestCase
{
    #[Test]
    public function it_does_not_register_the_webhook_route_by_default(): void
    {
        $this->assertFalse(Route::has('fast2sms.webhook'));

        $this->postJson('/fast2sms/webhook/any-secret', ['status' => 'delivered'])
            ->assertNotFound();
    }

    #[Test]
    public function it_binds_the_default_webhook_handler(): void
    {
        $this->assertInstanceOf(WebhookHandler::class, $this->app->make(WebhookHandlerInterface::class));
    }

    #[Test]
    public function it_exposes_the_webhook_handler_on_the_facade(): void
    {
        $this->assertInstanceOf(WebhookHandlerInterface::class, Fast2sms::webhook());
    }

    #[Test]
    public function it_ships_safe_webhook_defaults(): void
    {
        $this->assertFalse(config('fast2sms.webhook.enabled'));
        $this->assertTrue(config('fast2sms.webhook.auto_route'));
        $this->assertSame('fast2sms/webhook', config('fast2sms.webhook.path'));
        $this->assertNull(config('fast2sms.webhook.secret'));
        $this->assertSame(['api'], config('fast2sms.webhook.middleware'));
        $this->assertSame([], config('fast2sms.webhook.allowed_ips'));
        $this->assertTrue(config('fast2sms.webhook.update_logs'));
    }
}
