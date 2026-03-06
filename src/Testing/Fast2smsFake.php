<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Testing;

use Closure;

use function count;

use DateTimeImmutable;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Http;

use function in_array;
use function is_array;

use PHPUnit\Framework\Assert;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\SmsRoute;

use Shakil\Fast2sms\Enums\WhatsAppType;

/**
 * Fake implementation for Fast2sms, analogous to Laravel's MailFake.
 *
 * Holds all assertion state and logic independently of the real service class.
 */
class Fast2smsFake
{
    /**
     * The collection of "sent" messages captured during faking (raw payloads, for backward compat).
     *
     * @var Collection<int, array<string, mixed>>
     */
    private readonly Collection $sentMessages;

    /**
     * Typed recorded SMS sends.
     *
     * @var list<RecordedSmsSend>
     */
    private array $recordedSms = [];

    /**
     * Typed recorded WhatsApp sends.
     *
     * @var list<RecordedWhatsAppSend>
     */
    private array $recordedWhatsApp = [];

    public function __construct()
    {
        $this->sentMessages = collect();
    }

    /**
     * Activate the fake by registering an Http::fake() handler that captures payloads.
     */
    public function activate(): void
    {
        $fake = $this;

        Http::fake([
            config('fast2sms.base_url') . '*' => function ($request) use ($fake) {
                $payload = [];
                $contentType = $request->header('content-type')[0] ?? '';

                if (str_contains($contentType, 'application/json')) {
                    $payload = json_decode((string) $request->body(), true, 512, JSON_THROW_ON_ERROR);
                } elseif (str_contains($contentType, 'multipart/form-data')) {
                    foreach ($request->data() as $part) {
                        $payload[$part['name']] = $part['contents'];
                    }
                } else {
                    $payload = $request->data();
                    if (! is_array($payload)) {
                        parse_str((string) $request->body(), $payload);
                    }
                    foreach ($payload as $key => $value) {
                        if (is_array($value) && isset($value['name'], $value['contents'])) {
                            $payload[$value['name']] = $value['contents'];
                            unset($payload[$key]);
                        }
                    }
                }

                // Normalise payload keys to avoid issues with different API formats
                if (isset($payload['to']) && ! isset($payload['numbers'])) {
                    $payload['numbers'] = $payload['to'];
                }
                if (isset($payload['phone_number_id']) && ! isset($payload['sender_id'])) {
                    $payload['sender_id'] = $payload['phone_number_id'];
                }

                $path = parse_url((string) $request->url(), PHP_URL_PATH) ?? '';
                $fake->recordMessage($payload, $path);

                $responseBody = [
                    'return' => true,
                    'success' => true,
                    'status' => true,
                    'message' => 'Message sent successfully (faked).',
                ];

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
     * Record a captured message payload (called from the Http::fake() closure).
     *
     * @param array<string, mixed> $payload
     */
    public function recordMessage(array $payload, string $path = ''): void
    {
        $this->sentMessages->push($payload);

        $now = new DateTimeImmutable();

        if ($this->isWhatsAppPath($path)) {
            $numbers = $payload['numbers'] ?? $payload['to'] ?? '';
            $this->recordedWhatsApp[] = new RecordedWhatsAppSend(
                parameters: new WhatsAppParameters(
                    to: is_array($numbers) ? $numbers : (string) $numbers,
                    type: isset($payload['type']) ? WhatsAppType::tryFrom((string) $payload['type']) : null,
                    body: $payload['body'] ?? $payload['message'] ?? null,
                    templateId: isset($payload['template_id']) ? (string) $payload['template_id'] : null,
                ),
                sentAt: $now,
            );
        } else {
            $numbers = $payload['numbers'] ?? $payload['to'] ?? [];
            $this->recordedSms[] = new RecordedSmsSend(
                parameters: new SmsParameters(
                    numbers: is_array($numbers) ? $numbers : [$numbers],
                    message: $payload['message'] ?? '',
                    route: SmsRoute::tryFrom($payload['route'] ?? '') ?? SmsRoute::QUICK,
                ),
                sentAt: $now,
            );
        }
    }

    /**
     * Reset all recorded sends.
     */
    public function reset(): void
    {
        $this->sentMessages->forget($this->sentMessages->keys()->all());
        $this->recordedSms = [];
        $this->recordedWhatsApp = [];
    }

    /** @return list<RecordedSmsSend> */
    public function sentSms(): array
    {
        return $this->recordedSms;
    }

    /** @return list<RecordedWhatsAppSend> */
    public function sentWhatsApp(): array
    {
        return $this->recordedWhatsApp;
    }

    /**
     * Assert that at least one SMS was sent, optionally matching a closure.
     *
     * @param (Closure(SmsParameters): bool)|null $callback
     */
    public function assertSmsSent(?Closure $callback = null): void
    {
        if (! $callback instanceof Closure) {
            Assert::assertNotEmpty(
                $this->recordedSms,
                'No SMS was sent.',
            );

            return;
        }

        $matched = array_filter(
            $this->recordedSms,
            static fn (RecordedSmsSend $r) => $callback($r->parameters),
        );

        Assert::assertNotEmpty($matched, 'No SMS matching the given criteria was sent.');
    }

    /**
     * Assert that no SMS was sent, optionally matching a closure.
     *
     * @param (Closure(SmsParameters): bool)|null $callback
     */
    public function assertSmsNotSent(?Closure $callback = null): void
    {
        if (! $callback instanceof Closure) {
            Assert::assertEmpty(
                $this->recordedSms,
                'An SMS was sent when none was expected.',
            );

            return;
        }

        $matched = array_filter(
            $this->recordedSms,
            static fn (RecordedSmsSend $r) => $callback($r->parameters),
        );

        Assert::assertEmpty($matched, 'An SMS matching the given criteria was sent when none was expected.');
    }

    /**
     * Assert that exactly $count SMS messages were sent.
     */
    public function assertSmsSentCount(int $count): void
    {
        Assert::assertCount(
            $count,
            $this->recordedSms,
            "Expected $count SMS message(s) to be sent, but " . count($this->recordedSms) . ' were sent.',
        );
    }

    /**
     * Assert that an SMS was sent to the given number.
     */
    public function assertSmsSentTo(string $number): void
    {
        $matched = array_filter(
            $this->recordedSms,
            static fn (RecordedSmsSend $r): bool => in_array($number, $r->parameters->numbers, true),
        );

        Assert::assertNotEmpty($matched, "No SMS was sent to number: $number.");
    }

    /**
     * Assert that an SMS was sent with the given message content.
     */
    public function assertSmsSentWithMessage(string $message): void
    {
        $matched = array_filter(
            $this->recordedSms,
            static fn (RecordedSmsSend $r): bool => $r->parameters->message === $message,
        );

        Assert::assertNotEmpty($matched, "No SMS was sent with message: \"$message\".");
    }

    /**
     * Assert that an SMS was sent using the given route.
     */
    public function assertSmsSentWithRoute(SmsRoute $route): void
    {
        $matched = array_filter(
            $this->recordedSms,
            static fn (RecordedSmsSend $r): bool => $r->parameters->route === $route,
        );

        Assert::assertNotEmpty($matched, "No SMS was sent with route: $route->value.");
    }

    // -------------------------------------------------------------------------
    // WhatsApp assertions
    // -------------------------------------------------------------------------

    /**
     * Assert that at least one WhatsApp message was sent, optionally matching a closure.
     *
     * @param (Closure(WhatsAppParameters): bool)|null $callback
     */
    public function assertWhatsAppSent(?Closure $callback = null): void
    {
        if (! $callback instanceof Closure) {
            Assert::assertNotEmpty(
                $this->recordedWhatsApp,
                'No WhatsApp message was sent.',
            );

            return;
        }

        $matched = array_filter(
            $this->recordedWhatsApp,
            static fn (RecordedWhatsAppSend $r) => $callback($r->parameters),
        );

        Assert::assertNotEmpty($matched, 'No WhatsApp message matching the given criteria was sent.');
    }

    /**
     * Assert that no WhatsApp message was sent, optionally matching a closure.
     *
     * @param (Closure(WhatsAppParameters): bool)|null $callback
     */
    public function assertWhatsAppNotSent(?Closure $callback = null): void
    {
        if (! $callback instanceof Closure) {
            Assert::assertEmpty(
                $this->recordedWhatsApp,
                'A WhatsApp message was sent when none was expected.',
            );

            return;
        }

        $matched = array_filter(
            $this->recordedWhatsApp,
            static fn (RecordedWhatsAppSend $r) => $callback($r->parameters),
        );

        Assert::assertEmpty($matched, 'A WhatsApp message matching the given criteria was sent when none was expected.');
    }

    /**
     * Assert that exactly $count WhatsApp messages were sent.
     */
    public function assertWhatsAppSentCount(int $count): void
    {
        Assert::assertCount(
            $count,
            $this->recordedWhatsApp,
            "Expected $count WhatsApp message(s) to be sent, but " . count($this->recordedWhatsApp) . ' were sent.',
        );
    }

    /**
     * Assert that a WhatsApp message was sent to the given number.
     */
    public function assertWhatsAppSentTo(string $number): void
    {
        $matched = array_filter(
            $this->recordedWhatsApp,
            static function (RecordedWhatsAppSend $r) use ($number): bool {
                $to = $r->parameters->to;

                return is_array($to) ? in_array($number, $to, true) : $to === $number;
            },
        );

        Assert::assertNotEmpty($matched, "No WhatsApp message was sent to number: $number.");
    }

    /**
     * Assert that a WhatsApp message was sent with the given type.
     */
    public function assertWhatsAppSentWithType(WhatsAppType $type): void
    {
        $matched = array_filter(
            $this->recordedWhatsApp,
            static fn (RecordedWhatsAppSend $r): bool => $r->parameters->type === $type,
        );

        Assert::assertNotEmpty($matched, "No WhatsApp message was sent with type: $type->value.");
    }

    /**
     * Assert that nothing was sent (neither SMS nor WhatsApp).
     */
    public function assertNothingSent(): void
    {
        Assert::assertEmpty(
            $this->recordedSms,
            'An SMS was sent when nothing was expected.',
        );

        Assert::assertEmpty(
            $this->recordedWhatsApp,
            'A WhatsApp message was sent when nothing was expected.',
        );
    }

    /**
     * Assert that the total number of sends (SMS + WhatsApp) equals $expected.
     */
    public function assertSentCount(int $expected): void
    {
        $total = count($this->recordedSms) + count($this->recordedWhatsApp);

        Assert::assertEquals(
            $expected,
            $total,
            "Expected $expected total send(s), but $total were recorded.",
        );
    }

    /**
     * Assert that a message was sent matching the given criteria.
     *
     * @param array<string, mixed>|Closure|null $callback
     */
    public function assertSent(array|Closure|null $callback = null): void
    {
        if ($callback === null) {
            Assert::assertGreaterThan(
                0,
                $this->sentMessages->count(),
                'No message was sent.',
            );

            return;
        }

        Assert::assertTrue(
            $this->sentMessages->contains(function (array $message) use ($callback) {
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
            'A message with the given criteria was not sent.',
        );
    }

    /**
     * Assert that no message was sent matching the given criteria.
     *
     * @param array<string, mixed>|Closure|null $callback
     */
    public function assertNotSent(array|Closure|null $callback = null): void
    {
        if ($callback === null) {
            Assert::assertEquals(
                0,
                $this->sentMessages->count(),
                'A message was sent when it should not have been.',
            );

            return;
        }

        Assert::assertFalse(
            $this->sentMessages->contains(function (array $message) use ($callback) {
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
            'A message with the given criteria was sent when it should not have been.',
        );
    }

    /**
     * Assert that exactly the given number of messages were sent.
     */
    public function assertSentTimes(int $count): void
    {
        Assert::assertEquals(
            $count,
            $this->sentMessages->count(),
            "Expected $count message(s) to be sent, but " . $this->sentMessages->count() . ' were sent.',
        );
    }

    /**
     * Get all captured sent messages (raw payloads).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function sentMessages(): Collection
    {
        return $this->sentMessages;
    }

    private function isWhatsAppPath(string $path): bool
    {
        return str_contains($path, 'whatsapp') || str_contains($path, 'waba');
    }
}
