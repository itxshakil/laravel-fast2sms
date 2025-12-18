<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Facades;

use Closure;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Responses\Fast2smsResponse;

/**
 * @method static \Shakil\Fast2sms\Fast2sms to(string|array $numbers)
 * @method static \Shakil\Fast2sms\Fast2sms message(string $message)
 * @method static \Shakil\Fast2sms\Fast2sms senderId(string $senderId)
 * @method static \Shakil\Fast2sms\Fast2sms route(SmsRoute $route)
 * @method static \Shakil\Fast2sms\Fast2sms entityId(string $entityId)
 * @method static \Shakil\Fast2sms\Fast2sms templateId(string $templateId)
 * @method static \Shakil\Fast2sms\Fast2sms variables(array $values)
 * @method static \Shakil\Fast2sms\Fast2sms flash(bool $flash = true)
 * @method static \Shakil\Fast2sms\Fast2sms schedule(string|DateTimeInterface $time)
 * @method static \Shakil\Fast2sms\Fast2sms language(SmsLanguage $language)
 * @method static Fast2smsResponse          send()
 * @method static Fast2smsResponse          quick(string|array $numbers, string $message, ?SmsLanguage $language = null)
 * @method static Fast2smsResponse          dlt(string|array $numbers, string $templateId, array|string $variablesValues, ?string $senderId = null, ?string $entityId = null)
 * @method static Fast2smsResponse          otp(string|array $numbers, string $otpValue)
 * @method static Fast2smsResponse          checkBalance()
 * @method static Fast2smsResponse          dltManager(string $type)
 * @method static void                      fake()
 * @method static void                      assertSent(array|Closure|null $callback = null)
 * @method static void                      assertNotSent(array|Closure|null $callback = null)
 * @method static void                      assertSentTimes(int $count)
 * @method static Collection                sentMessages()
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
