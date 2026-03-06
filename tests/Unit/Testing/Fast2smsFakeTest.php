<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Testing;

use PHPUnit\Framework\AssertionFailedError;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Testing\Fast2smsFake;
use Shakil\Fast2sms\Tests\TestCase;

class Fast2smsFakeTest extends TestCase
{
    private Fast2smsFake $fake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fake = new Fast2smsFake();
    }

    public function test_it_starts_with_no_sent_messages(): void
    {
        $this->assertCount(0, $this->fake->sentMessages());
    }

    public function test_it_records_sent_messages(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'Hello']);
        $this->fake->recordMessage(['numbers' => '8888888888', 'message' => 'World']);

        $this->assertCount(2, $this->fake->sentMessages());
    }

    public function test_assert_sent_passes_when_message_was_sent(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'Hello']);

        $this->fake->assertSent();
    }

    public function test_assert_sent_fails_when_no_message_was_sent(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->fake->assertSent();
    }

    public function test_assert_sent_with_array_criteria_passes_on_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'route' => 'q']);

        $this->fake->assertSent(['numbers' => '9999999999', 'route' => 'q']);
    }

    public function test_assert_sent_with_array_criteria_fails_on_no_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'route' => 'q']);

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertSent(['numbers' => '1111111111']);
    }

    public function test_assert_sent_with_closure_passes_on_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'OTP: 123456']);

        $this->fake->assertSent(fn (array $msg) => str_contains($msg['message'], 'OTP'));
    }

    public function test_assert_not_sent_passes_when_no_message_was_sent(): void
    {
        $this->fake->assertNotSent();
    }

    public function test_assert_not_sent_fails_when_message_was_sent(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999']);

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertNotSent();
    }

    public function test_assert_sent_times_passes_with_correct_count(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999']);
        $this->fake->recordMessage(['numbers' => '8888888888']);

        $this->fake->assertSentTimes(2);
    }

    public function test_assert_sent_times_fails_with_wrong_count(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999']);

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertSentTimes(3);
    }

    public function test_it_resets_state_on_each_new_instance(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999']);

        $newFake = new Fast2smsFake();

        $this->assertCount(0, $newFake->sentMessages());
    }

    public function test_sent_messages_returns_collection(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999']);

        $messages = $this->fake->sentMessages();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $messages);
        $this->assertSame('9999999999', $messages->first()['numbers']);
    }

    public function test_sent_whatsapp_returns_empty_array_initially(): void
    {
        $this->assertSame([], $this->fake->sentWhatsApp());
    }

    public function test_sent_whatsapp_returns_recorded_whatsapp_sends(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text', 'body' => 'Hello'], '/whatsapp/send');
        $this->fake->recordMessage(['numbers' => '8888888888', 'type' => 'image'], '/whatsapp/send');

        $this->assertCount(2, $this->fake->sentWhatsApp());
    }

    public function test_assert_whatsapp_sent_passes_when_message_was_sent(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->fake->assertWhatsAppSent();
    }

    public function test_assert_whatsapp_sent_fails_when_no_message_was_sent(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppSent();
    }

    public function test_assert_whatsapp_sent_with_closure_passes_on_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text', 'body' => 'Hello'], '/whatsapp/send');

        $this->fake->assertWhatsAppSent(fn (WhatsAppParameters $p) => $p->body === 'Hello');
    }

    public function test_assert_whatsapp_sent_with_closure_fails_on_no_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text', 'body' => 'Hello'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppSent(fn (WhatsAppParameters $p) => $p->body === 'Goodbye');
    }

    public function test_assert_whatsapp_not_sent_passes_when_no_message_was_sent(): void
    {
        $this->fake->assertWhatsAppNotSent();
    }

    public function test_assert_whatsapp_not_sent_fails_when_message_was_sent(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppNotSent();
    }

    public function test_assert_whatsapp_not_sent_with_closure_passes_when_no_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text', 'body' => 'Hello'], '/whatsapp/send');

        $this->fake->assertWhatsAppNotSent(fn (WhatsAppParameters $p) => $p->body === 'Goodbye');
    }

    public function test_assert_whatsapp_not_sent_with_closure_fails_when_match_found(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text', 'body' => 'Hello'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppNotSent(fn (WhatsAppParameters $p) => $p->body === 'Hello');
    }

    public function test_assert_whatsapp_sent_count_passes_with_correct_count(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');
        $this->fake->recordMessage(['numbers' => '8888888888', 'type' => 'image'], '/whatsapp/send');

        $this->fake->assertWhatsAppSentCount(2);
    }

    public function test_assert_whatsapp_sent_count_fails_with_wrong_count(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppSentCount(3);
    }

    public function test_assert_whatsapp_sent_to_passes_when_number_matches(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->fake->assertWhatsAppSentTo('9999999999');
    }

    public function test_assert_whatsapp_sent_to_fails_when_number_does_not_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppSentTo('1111111111');
    }

    public function test_assert_whatsapp_sent_with_type_passes_when_type_matches(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->fake->assertWhatsAppSentWithType(WhatsAppType::TEXT);
    }

    public function test_assert_whatsapp_sent_with_type_fails_when_type_does_not_match(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWhatsAppSentWithType(WhatsAppType::IMAGE);
    }

    public function test_assert_nothing_sent_passes_when_nothing_was_sent(): void
    {
        $this->fake->assertNothingSent();
    }

    public function test_assert_nothing_sent_fails_when_sms_was_sent(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'Hello']);

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertNothingSent();
    }

    public function test_assert_nothing_sent_fails_when_whatsapp_was_sent(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'type' => 'text'], '/whatsapp/send');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertNothingSent();
    }

    public function test_assert_sent_count_passes_with_correct_total(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'Hello']);
        $this->fake->recordMessage(['numbers' => '8888888888', 'type' => 'text'], '/whatsapp/send');

        $this->fake->assertSentCount(2);
    }

    public function test_assert_sent_count_fails_with_wrong_total(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'Hello']);

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertSentCount(5);
    }

    public function test_assert_sent_count_counts_both_sms_and_whatsapp(): void
    {
        $this->fake->recordMessage(['numbers' => '9999999999', 'message' => 'Hello']);
        $this->fake->recordMessage(['numbers' => '8888888888', 'message' => 'World']);
        $this->fake->recordMessage(['numbers' => '7777777777', 'type' => 'text'], '/whatsapp/send');

        $this->fake->assertSentCount(3);
    }
}
