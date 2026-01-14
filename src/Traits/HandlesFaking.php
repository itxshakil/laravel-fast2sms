<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

use function is_array;

use PHPUnit\Framework\Assert;

use Shakil\Fast2sms\Exceptions\Fast2smsException;

/**
 * Trait to handle faking and assertion for Fast2sms during testing.
 */
trait HandlesFaking
{
    /**
     * Indicates if the Fast2sms service is faking SMS sends.
     */
    protected static bool $faking = false;

    /**
     * The collection of "sent" messages when faking.
     *
     * @var Collection<int, array<string, mixed>>
     */
    protected static Collection $sentMessages;

    /**
     * Enable faking for Fast2sms.
     * This will prevent actual HTTP calls and store messages in memory.
     */
    public static function fake(): void
    {
        self::$faking = true;
        self::$sentMessages = collect();

        Http::fake([
            config('fast2sms.base_url') . '*' => function ($request) {
                // Convert multipart request into array
                $payload = [];
                foreach ($request->data() as $part) {
                    $payload[$part['name']] = $part['contents'];
                }

                self::$sentMessages->push($payload);

                return Http::response([
                    'return' => true,
                    'message' => 'SMS sent successfully (faked).',
                ]);
            },
        ]);
    }

    /**
     * Assert that an SMS was sent.
     *
     * @param array<string, mixed>|Closure|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertSent(array|Closure|null $callback = null): void
    {
        if (! self::$faking) {
            throw new Fast2smsException('Fast2sms is not in faking mode. Call Fast2sms::fake() first.');
        }

        if ($callback === null) {
            Assert::assertGreaterThan(
                0,
                self::$sentMessages->count(),
                'No SMS was sent.',
            );

            return;
        }

        Assert::assertTrue(
            self::$sentMessages->contains(function (array $message) use ($callback) {
                if (is_array($callback)) {
                    foreach ($callback as $key => $value) {
                        if (! isset($message[$key]) || $message[$key] !== $value) {
                            return false;
                        }
                    }

                    return true;
                }

                return $callback($message);
            }),
            'An SMS with the given criteria was not sent.',
        );
    }

    /**
     * Assert that an SMS was not sent.
     *
     * @param array<string, mixed>|Closure|null $callback
     *
     * @throws Fast2smsException
     */
    public static function assertNotSent(array|Closure|null $callback = null): void
    {
        if (! self::$faking) {
            throw new Fast2smsException('Fast2sms is not in faking mode. Call Fast2sms::fake() first.');
        }

        if ($callback === null) {
            Assert::assertEquals(
                0,
                self::$sentMessages->count(),
                'SMS was sent when it should not have been.',
            );

            return;
        }

        Assert::assertFalse(
            self::$sentMessages->contains(function (array $message) use ($callback) {
                if (is_array($callback)) {
                    foreach ($callback as $key => $value) {
                        if (! isset($message[$key]) || $message[$key] !== $value) {
                            return false;
                        }
                    }

                    return true;
                }

                return $callback($message);
            }),
            'An SMS with the given criteria was sent when it should not have been.',
        );
    }

    /**
     * Assert that a specific number of SMS messages were sent.
     *
     * @throws Fast2smsException
     */
    public static function assertSentTimes(int $count): void
    {
        if (! self::$faking) {
            throw new Fast2smsException('Fast2sms is not in faking mode. Call Fast2sms::fake() first.');
        }

        Assert::assertEquals(
            $count,
            self::$sentMessages->count(),
            "Expected $count SMS messages to be sent, but " . self::$sentMessages->count() . ' were sent.',
        );
    }

    /**
     * Get all "sent" messages when faking.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function sentMessages(): Collection
    {
        return self::$sentMessages;
    }
}
