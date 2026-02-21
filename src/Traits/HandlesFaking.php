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
                // Determine if it's a multipart (SMS) or JSON (WhatsApp) request
                $payload = [];
                $contentType = $request->header('content-type')[0] ?? '';

                if (str_contains($contentType, 'application/json')) {
                    $payload = json_decode($request->body(), true, 512, JSON_THROW_ON_ERROR);
                } elseif (str_contains($contentType, 'multipart/form-data')) {
                    foreach ($request->data() as $part) {
                        $payload[$part['name']] = $part['contents'];
                    }
                } else {
                    $payload = $request->data();
                    if (! is_array($payload)) {
                        parse_str($request->body(), $payload);
                    }
                    if (is_array($payload)) {
                        foreach ($payload as $key => $value) {
                            if (is_array($value) && isset($value['name'], $value['contents'])) {
                                $payload[$value['name']] = $value['contents'];
                                unset($payload[$key]);
                            }
                        }
                    }
                }

                // Normalizing payload keys to avoid issues with different API formats
                if (isset($payload['to']) && ! isset($payload['numbers'])) {
                    $payload['numbers'] = $payload['to'];
                }
                if (isset($payload['phone_number_id']) && ! isset($payload['sender_id'])) {
                    $payload['sender_id'] = $payload['phone_number_id'];
                }

                self::$sentMessages->push($payload);

                $responseBody = [
                    'return' => true,
                    'success' => true,
                    'status' => true,
                    'message' => 'Message sent successfully (faked).',
                ];

                // Mock data for specific endpoints to support various tests
                $path = parse_url($request->url(), PHP_URL_PATH);
                if (str_contains($path, 'whatsapp-waba') || str_contains($path, 'dlt_manager/whatsapp')) {
                    $responseBody['data'] = [['id' => 'mock_id']];
                } elseif (str_contains($path, 'media')) {
                    $responseBody['id'] = 'MEDIA_ID';
                }

                return Http::response($responseBody);
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
