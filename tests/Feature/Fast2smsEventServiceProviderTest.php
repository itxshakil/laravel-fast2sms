<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;

use function is_array;
use function is_object;
use function is_string;

use ReflectionException;
use ReflectionFunction;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Listeners\LogSmsFailed;
use Shakil\Fast2sms\Listeners\LogSmsSent;
use Shakil\Fast2sms\Listeners\LogWhatsAppFailed;
use Shakil\Fast2sms\Listeners\LogWhatsAppSent;
use Shakil\Fast2sms\Tests\TestCase;

class Fast2smsEventServiceProviderTest extends TestCase
{
    public function test_sms_sent_event_is_wired_to_log_sms_sent_listener(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);
        $listeners = $dispatcher->getListeners(SmsSent::class);

        $listenerClasses = $this->extractListenerClasses($listeners);

        $this->assertContains(LogSmsSent::class, $listenerClasses);
    }

    public function test_sms_failed_event_is_wired_to_log_sms_failed_listener(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);
        $listeners = $dispatcher->getListeners(SmsFailed::class);

        $listenerClasses = $this->extractListenerClasses($listeners);

        $this->assertContains(LogSmsFailed::class, $listenerClasses);
    }

    public function test_whatsapp_sent_event_is_wired_to_log_whatsapp_sent_listener(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);
        $listeners = $dispatcher->getListeners(WhatsAppSent::class);

        $listenerClasses = $this->extractListenerClasses($listeners);

        $this->assertContains(LogWhatsAppSent::class, $listenerClasses);
    }

    public function test_whatsapp_failed_event_is_wired_to_log_whatsapp_failed_listener(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);
        $listeners = $dispatcher->getListeners(WhatsAppFailed::class);

        $listenerClasses = $this->extractListenerClasses($listeners);

        $this->assertContains(LogWhatsAppFailed::class, $listenerClasses);
    }

    public function test_all_four_events_have_exactly_one_listener_each(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        foreach ([SmsSent::class, SmsFailed::class, WhatsAppSent::class, WhatsAppFailed::class] as $event) {
            $listeners = $dispatcher->getListeners($event);
            $this->assertCount(1, $listeners, "Expected exactly 1 listener for {$event}.");
        }
    }

    /**
     * Extract class names from the raw listener callables returned by the dispatcher.
     *
     * @param  array<int, callable> $listeners
     * @return list<string>
     *
     * @throws ReflectionException
     */
    private function extractListenerClasses(array $listeners): array
    {
        $classes = [];

        foreach ($listeners as $listener) {
            if (is_array($listener) && isset($listener[0])) {
                $classes[] = is_object($listener[0]) ? $listener[0]::class : $listener[0];
            } elseif (is_string($listener)) {
                $classes[] = $listener;
            } elseif ($listener instanceof Closure) {
                // Wrap closures — inspect via reflection to find bound class
                $ref = new ReflectionFunction($listener);
                $innerListener = $ref->getStaticVariables()['listener'] ?? null;
                if (is_string($innerListener)) {
                    $classes[] = $innerListener;
                } elseif (is_array($innerListener) && isset($innerListener[0])) {
                    $classes[] = is_object($innerListener[0]) ? $innerListener[0]::class : $innerListener[0];
                }
            }
        }

        return $classes;
    }
}
