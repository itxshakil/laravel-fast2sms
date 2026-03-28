<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Events\LowBalanceDetected;
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;
use Shakil\Fast2sms\Tests\TestCase;

class CostSavingFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['fast2sms.api_key' => 'test-api-key']);
        $this->baseUrl = config('fast2sms.base_url');
    }

    #[Test]
    public function it_returns_correct_char_count(): void
    {
        $msg = new SmsMessage('Hello');
        $this->assertSame(5, $msg->charCount());
    }

    #[Test]
    public function it_detects_ascii_as_non_unicode(): void
    {
        $msg = new SmsMessage('Hello World');
        $this->assertFalse($msg->isUnicode());
    }

    #[Test]
    public function it_detects_unicode_characters(): void
    {
        $msg = new SmsMessage('नमस्ते');
        $this->assertTrue($msg->isUnicode());
    }

    #[Test]
    public function it_returns_one_credit_for_short_gsm_message(): void
    {
        $msg = new SmsMessage(str_repeat('A', 160));
        $this->assertSame(1, $msg->creditCount());
        $this->assertFalse($msg->exceedsOneSms());
    }

    #[Test]
    public function it_returns_two_credits_for_long_gsm_message(): void
    {
        $msg = new SmsMessage(str_repeat('A', 161));
        $this->assertSame(2, $msg->creditCount());
        $this->assertTrue($msg->exceedsOneSms());
    }

    #[Test]
    public function it_returns_one_credit_for_short_unicode_message(): void
    {
        // 70 chars of unicode
        $msg = new SmsMessage(str_repeat('é', 70));
        $this->assertSame(1, $msg->creditCount());
    }

    #[Test]
    public function it_returns_two_credits_for_long_unicode_message(): void
    {
        $msg = new SmsMessage(str_repeat('é', 71));
        $this->assertSame(2, $msg->creditCount());
        $this->assertTrue($msg->exceedsOneSms());
    }

    #[Test]
    public function it_deduplicates_recipient_numbers_before_sending(): void
    {
        config(['fast2sms.recipients.deduplicate' => true]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'dedup-1']),
        ]);

        Fast2sms::to(['9999999999', '9999999999', '8888888888'])
            ->message('Dedup test')
            ->send();

        Http::assertSent(function ($request) {
            $numbers = $request->data()['numbers'] ?? '';

            return $numbers === '9999999999,8888888888';
        });
    }

    #[Test]
    public function it_skips_deduplication_when_disabled(): void
    {
        config(['fast2sms.recipients.deduplicate' => false]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'no-dedup']),
        ]);

        Fast2sms::to(['9999999999', '9999999999'])
            ->message('No dedup test')
            ->send();

        Http::assertSent(function ($request) {
            $numbers = $request->data()['numbers'] ?? '';

            return $numbers === '9999999999,9999999999';
        });
    }

    #[Test]
    public function it_strips_invalid_recipients_and_sends_to_valid_ones(): void
    {
        config(['fast2sms.validation.strip_invalid_recipients' => true]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'strip-1']),
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn ($msg, $ctx) => str_contains($msg, 'Stripped invalid') && $ctx['number'] === '1234');

        Fast2sms::to(['9876543210', '1234'])
            ->message('Strip test')
            ->send();

        Http::assertSent(function ($request) {
            return ($request->data()['numbers'] ?? '') === '9876543210';
        });
    }

    #[Test]
    public function it_throws_when_all_recipients_are_invalid(): void
    {
        config(['fast2sms.validation.strip_invalid_recipients' => true]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'never']),
        ]);

        Log::shouldReceive('warning')->once();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('All recipient numbers are invalid');

        Fast2sms::to(['1234'])->message('Bad numbers')->send();
    }

    #[Test]
    public function it_throws_on_duplicate_send_within_ttl(): void
    {
        config([
            'fast2sms.deduplication.enabled' => true,
            'fast2sms.deduplication.ttl' => 60,
        ]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'first']),
        ]);

        Fast2sms::to('9876543210')->message('Dedup guard')->send();

        $this->expectException(DuplicateSendException::class);

        Fast2sms::to('9876543210')->message('Dedup guard')->send();
    }

    #[Test]
    public function it_allows_send_after_dedup_ttl_expires(): void
    {
        config([
            'fast2sms.deduplication.enabled' => true,
            'fast2sms.deduplication.ttl' => 60,
        ]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'ok']),
        ]);

        // Manually clear the cache key to simulate TTL expiry
        Cache::flush();

        $response = Fast2sms::to('9876543210')->message('After TTL')->send();
        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_throws_when_throttle_limit_is_exceeded(): void
    {
        config([
            'fast2sms.throttle.enabled' => true,
            'fast2sms.throttle.max_per_minute' => 2,
        ]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'ok']),
        ]);

        Fast2sms::to('9876543210')->message('Throttle 1')->send();

        // Reset dedup so second send is not blocked by dedup
        config(['fast2sms.deduplication.enabled' => false]);

        Fast2sms::to('9876543211')->message('Throttle 2')->send();

        $this->expectException(ThrottleExceededException::class);

        Fast2sms::to('9876543212')->message('Throttle 3')->send();
    }

    #[Test]
    public function it_does_not_throttle_when_disabled(): void
    {
        config(['fast2sms.throttle.enabled' => false]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'ok']),
        ]);

        // Should not throw regardless of how many sends
        for ($i = 0; $i < 5; $i++) {
            $response = Fast2sms::to('987654321' . $i)->message('No throttle')->send();
            $this->assertTrue($response->isSuccess());
        }
    }

    #[Test]
    public function it_fires_low_balance_event_and_aborts_when_balance_is_below_threshold(): void
    {
        config([
            'fast2sms.balance_gate.enabled' => true,
            'fast2sms.balance_gate.threshold' => 100.0,
            'fast2sms.balance_gate.abort' => true,
        ]);

        Event::fake([LowBalanceDetected::class]);

        Http::fake([
            $this->baseUrl . '/wallet' => Http::response(['return' => true, 'wallet' => '5.00', 'sms_count' => 10]),
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'never']),
        ]);

        $this->expectException(InsufficientBalanceException::class);

        Fast2sms::to('9876543210')->message('Balance gate test')->send();

        Event::assertDispatched(LowBalanceDetected::class);
    }

    #[Test]
    public function it_fires_low_balance_event_but_continues_when_abort_is_false(): void
    {
        config([
            'fast2sms.balance_gate.enabled' => true,
            'fast2sms.balance_gate.threshold' => 100.0,
            'fast2sms.balance_gate.abort' => false,
        ]);

        Event::fake([LowBalanceDetected::class]);

        Http::fake([
            $this->baseUrl . '/wallet' => Http::response(['return' => true, 'wallet' => '5.00', 'sms_count' => 10]),
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'sent-anyway']),
        ]);

        $response = Fast2sms::to('9876543210')->message('Low balance no abort')->send();

        $this->assertTrue($response->isSuccess());
        Event::assertDispatched(LowBalanceDetected::class);
    }

    #[Test]
    public function it_does_not_fire_low_balance_event_when_balance_is_sufficient(): void
    {
        config([
            'fast2sms.balance_gate.enabled' => true,
            'fast2sms.balance_gate.threshold' => 10.0,
            'fast2sms.balance_gate.abort' => true,
        ]);

        Event::fake([LowBalanceDetected::class]);

        Http::fake([
            $this->baseUrl . '/wallet' => Http::response(['return' => true, 'wallet' => '500.00', 'sms_count' => 1000]),
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'ok']),
        ]);

        $response = Fast2sms::to('9876543210')->message('Sufficient balance')->send();

        $this->assertTrue($response->isSuccess());
        Event::assertNotDispatched(LowBalanceDetected::class);
    }

    #[Test]
    public function it_splits_recipients_into_batches_and_makes_multiple_api_calls(): void
    {
        config(['fast2sms.recipients.batch_size' => 2]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'batch']),
        ]);

        Fast2sms::to(['9876543210', '9876543211', '9876543212'])
            ->message('Batch test')
            ->send();

        // 3 numbers with batch_size=2 → 2 API calls
        Http::assertSentCount(2);
    }

    #[Test]
    public function it_sends_in_one_call_when_batch_size_is_zero(): void
    {
        config(['fast2sms.recipients.batch_size' => 0]);

        Http::fake([
            $this->baseUrl . '*' => Http::response(['return' => true, 'request_id' => 'single']),
        ]);

        Fast2sms::to(['9876543210', '9876543211', '9876543212'])
            ->message('No batch')
            ->send();

        Http::assertSentCount(1);
    }
}
