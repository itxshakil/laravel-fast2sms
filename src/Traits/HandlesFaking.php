<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Closure;
use Illuminate\Support\Collection;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Testing\Fast2smsFake;
use Shakil\Fast2sms\Testing\RecordedSmsSend;
use Shakil\Fast2sms\Testing\RecordedWhatsAppSend;

/**
 * Handles faking and assertion for Fast2sms during testing.
 */
trait HandlesFaking
{
    protected static ?Fast2smsFake $fake = null;

    public static function fake(): Fast2smsFake
    {
        self::$fake = new Fast2smsFake();
        self::$fake->activate();

        return self::$fake;
    }

    /**
     * Stop faking and reset the shared fake instance.
     *
     * Call this in your test's tearDown() to prevent fake state from leaking
     * into subsequent test cases.
     */
    public static function stopFaking(): void
    {
        self::$fake = null;
    }

    /**
     * @param array<string, mixed>|Closure|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertSent(array|Closure|null $callback = null): void
    {
        self::ensureFaking();
        self::$fake->assertSent($callback);
    }

    /**
     * @param array<string, mixed>|Closure|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertNotSent(array|Closure|null $callback = null): void
    {
        self::ensureFaking();
        self::$fake->assertNotSent($callback);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertSentTimes(int $count): void
    {
        self::ensureFaking();
        self::$fake->assertSentTimes($count);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     *
     * @throws Fast2smsException
     */
    public static function sentMessages(): Collection
    {
        self::ensureFaking();

        return self::$fake->sentMessages();
    }

    /**
     * @return list<RecordedSmsSend>
     *
     * @throws Fast2smsException
     */
    public static function sentSms(): array
    {
        self::ensureFaking();

        return self::$fake->sentSms();
    }

    /**
     * @return list<RecordedWhatsAppSend>
     *
     * @throws Fast2smsException
     */
    public static function sentWhatsApp(): array
    {
        self::ensureFaking();

        return self::$fake->sentWhatsApp();
    }

    /**
     * @param (Closure(SmsParameters): bool)|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertSmsSent(?Closure $callback = null): void
    {
        self::ensureFaking();
        self::$fake->assertSmsSent($callback);
    }

    /**
     * @param (Closure(SmsParameters): bool)|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertSmsNotSent(?Closure $callback = null): void
    {
        self::ensureFaking();
        self::$fake->assertSmsNotSent($callback);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertSmsSentCount(int $count): void
    {
        self::ensureFaking();
        self::$fake->assertSmsSentCount($count);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertSmsSentTo(string $number): void
    {
        self::ensureFaking();
        self::$fake->assertSmsSentTo($number);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertSmsSentWithMessage(string $message): void
    {
        self::ensureFaking();
        self::$fake->assertSmsSentWithMessage($message);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertSmsSentWithRoute(SmsRoute $route): void
    {
        self::ensureFaking();
        self::$fake->assertSmsSentWithRoute($route);
    }

    /**
     * @param (Closure(WhatsAppParameters): bool)|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertWhatsAppSent(?Closure $callback = null): void
    {
        self::ensureFaking();
        self::$fake->assertWhatsAppSent($callback);
    }

    /**
     * @param (Closure(WhatsAppParameters): bool)|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertWhatsAppNotSent(?Closure $callback = null): void
    {
        self::ensureFaking();
        self::$fake->assertWhatsAppNotSent($callback);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertWhatsAppSentCount(int $count): void
    {
        self::ensureFaking();
        self::$fake->assertWhatsAppSentCount($count);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertWhatsAppSentTo(string $number): void
    {
        self::ensureFaking();
        self::$fake->assertWhatsAppSentTo($number);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertWhatsAppSentWithType(WhatsAppType $type): void
    {
        self::ensureFaking();
        self::$fake->assertWhatsAppSentWithType($type);
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertNothingSent(): void
    {
        self::ensureFaking();
        self::$fake->assertNothingSent();
    }

    /**
     * @throws Fast2smsException
     */
    public static function assertSentCount(int $expected): void
    {
        self::ensureFaking();
        self::$fake->assertSentCount($expected);
    }

    /**
     * @throws Fast2smsException
     */
    private static function ensureFaking(): void
    {
        if (! self::$fake instanceof Fast2smsFake) {
            throw new Fast2smsException('Fast2sms is not in faking mode. Call Fast2sms::fake() first.');
        }
    }
}
