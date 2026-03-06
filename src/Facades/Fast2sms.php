<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Facades;

use Closure;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Testing\Fast2smsFake;
use Shakil\Fast2sms\Testing\RecordedSmsSend;
use Shakil\Fast2sms\Testing\RecordedWhatsAppSend;

/**
 * @method static \Shakil\Fast2sms\Fast2sms             to(string|array<int, string> $numbers)
 * @method static \Shakil\Fast2sms\Fast2sms             message(string $message)
 * @method static \Shakil\Fast2sms\Fast2sms             senderId(string $senderId)
 * @method static \Shakil\Fast2sms\Fast2sms             route(SmsRoute $route)
 * @method static \Shakil\Fast2sms\Fast2sms             entityId(string $entityId)
 * @method static \Shakil\Fast2sms\Fast2sms             templateId(string $templateId)
 * @method static \Shakil\Fast2sms\Fast2sms             variables(array<int|string, mixed> $values)
 * @method static \Shakil\Fast2sms\Fast2sms             flash(bool $flash = true)
 * @method static \Shakil\Fast2sms\Fast2sms             schedule(string|DateTimeInterface $time)
 * @method static \Shakil\Fast2sms\Fast2sms             language(SmsLanguage $language)
 * @method static ResponseInterface                     send()
 * @method static ResponseInterface                     quick(string|array<int, string> $numbers, string $message, ?SmsLanguage $language = null)
 * @method static ResponseInterface                     dlt(string|array<int, string> $numbers, string $templateId, array<int|string, mixed>|string $variablesValues, ?string $senderId = null, ?string $entityId = null)
 * @method static ResponseInterface                     otp(string|array<int, string> $numbers, string $otpValue)
 * @method static ResponseInterface                     checkBalance()
 * @method static ResponseInterface                     dltManager(string $type)
 * @method static WhatsAppInterface                     whatsapp()
 * @method static WhatsAppInterface                     viaWhatsApp(string|array<int, string>|null $to = null)
 * @method static void                                  queue()
 * @method static \Shakil\Fast2sms\Fast2sms             onConnection(string $connection)
 * @method static \Shakil\Fast2sms\Fast2sms             onQueue(string $queue)
 * @method static \Shakil\Fast2sms\Fast2sms             delay(int $seconds)
 * @method static Fast2smsFake                          fake()
 * @method static array<class-string, string>           events()
 * @method static void                                  assertSent(array<string, mixed>|Closure|null $callback = null)
 * @method static void                                  assertNotSent(array<string, mixed>|Closure|null $callback = null)
 * @method static void                                  assertSentTimes(int $count)
 * @method static void                                  assertSmsSent(Closure|null $callback = null)
 * @method static void                                  assertSmsNotSent(Closure|null $callback = null)
 * @method static void                                  assertSmsSentCount(int $count)
 * @method static void                                  assertSmsSentTo(string $number)
 * @method static void                                  assertSmsSentWithMessage(string $message)
 * @method static void                                  assertSmsSentWithRoute(SmsRoute $route)
 * @method static void                                  assertWhatsAppSent(Closure|null $callback = null)
 * @method static void                                  assertWhatsAppNotSent(Closure|null $callback = null)
 * @method static void                                  assertWhatsAppSentCount(int $count)
 * @method static void                                  assertWhatsAppSentTo(string $number)
 * @method static void                                  assertWhatsAppSentWithType(WhatsAppType $type)
 * @method static void                                  assertNothingSent()
 * @method static void                                  assertSentCount(int $expected)
 * @method static Collection<int, array<string, mixed>> sentMessages()
 * @method static list<RecordedSmsSend>                 sentSms()
 * @method static list<RecordedWhatsAppSend>            sentWhatsApp()
 *
 * @see \Shakil\Fast2sms\Fast2sms
 */
class Fast2sms extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'fast2sms';
    }
}
