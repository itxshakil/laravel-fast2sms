<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Listeners\LogSmsFailed;
use Shakil\Fast2sms\Listeners\LogSmsSent;
use Shakil\Fast2sms\Listeners\LogWhatsAppFailed;
use Shakil\Fast2sms\Listeners\LogWhatsAppSent;

/**
 * Dedicated event service provider for Fast2sms events.
 *
 * Extracted from Fast2smsServiceProvider to keep each provider
 * focused on a single responsibility.
 */
class Fast2smsEventServiceProvider extends EventServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SmsSent::class => [LogSmsSent::class],
        SmsFailed::class => [LogSmsFailed::class],
        WhatsAppSent::class => [LogWhatsAppSent::class],
        WhatsAppFailed::class => [LogWhatsAppFailed::class],
    ];
}
