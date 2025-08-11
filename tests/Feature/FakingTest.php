<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Tests\TestCase;

class FakingTest extends TestCase
{
    private string $testNumber = '9999999999';

    /**
     * @throws Fast2smsException
     */
    #[Test]
    public function it_can_fake_an_sms_send(): void
    {
        Fast2sms::fake();

        Fast2sms::quick($this->testNumber, 'This is a faked message.');

        Fast2sms::assertSent();
        Fast2sms::assertSentTimes(1);
    }

    #[Test]
    public function it_can_assert_that_no_sms_was_sent(): void
    {
        Fast2sms::fake();

        Fast2sms::assertNotSent();
        Fast2sms::assertSentTimes(0);
    }

    #[Test]
    public function it_can_assert_that_an_sms_was_sent_with_a_specific_closure(): void
    {
        Fast2sms::fake();

        Fast2sms::quick($this->testNumber, 'Important message.');

        Fast2sms::assertSent(function ($message) {
            return $message['numbers'] === $this->testNumber
                && $message['message'] === 'Important message.';
        });
    }

    #[Test]
    public function it_can_assert_that_an_sms_was_sent_with_a_specific_array_subset(): void
    {
        Fast2sms::fake();

        Fast2sms::quick($this->testNumber, 'Important message.');

        Fast2sms::assertSent([
            'numbers' => $this->testNumber,
            'message' => 'Important message.',
        ]);
    }

    #[Test]
    public function it_can_assert_a_specific_number_of_sms_were_sent(): void
    {
        Fast2sms::fake();

        Fast2sms::quick($this->testNumber, 'Message 1.');
        Fast2sms::quick($this->testNumber, 'Message 2.');

        Fast2sms::assertSentTimes(2);
    }

    #[Test]
    public function it_can_assert_that_a_message_was_not_sent_with_a_closure(): void
    {
        Fast2sms::fake();

        Fast2sms::quick($this->testNumber, 'Message to be sent.');

        Fast2sms::assertNotSent(function ($message) {
            return $message['message'] === 'This message was not sent.';
        });
    }
}
