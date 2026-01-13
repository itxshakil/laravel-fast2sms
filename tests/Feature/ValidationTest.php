<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Tests\TestCase;
use TypeError;

class ValidationTest extends TestCase
{
    private string $testNumber = '9999999999';

    private string $testSenderId = 'FASTSM';

    private string $testTemplateId = '1234567890123456';

    protected function setUp(): void
    {
        parent::setUp();
        config(['fast2sms.api_key' => 'test-api-key']);
    }

    // --- Global Validation ---

    #[Test]
    public function it_throws_an_exception_if_api_key_is_missing(): void
    {
        config(['fast2sms.api_key' => '']);
        config(['fast2sms.driver' => 'api']);

        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Fast2sms API Key is not configured. Please set FAST2SMS_API_KEY in your .env file.');

        $provider = new \Shakil\Fast2sms\Fast2smsServiceProvider(app());
        $provider->boot();
    }

    #[Test]
    public function it_throws_an_exception_if_base_url_is_missing(): void
    {
        config(['fast2sms.base_url' => '']);

        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Fast2sms base_url is not configured');

        $provider = new \Shakil\Fast2sms\Fast2smsServiceProvider(app());
        $provider->boot();
    }

    #[Test]
    public function it_throws_an_exception_if_recipient_number_is_missing(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Recipient number(s) are required. Use ->to().');

        Fast2sms::message('Test')->send();
    }

    // --- Quick SMS Validation ---

    #[Test]
    public function it_throws_an_exception_if_message_is_missing_for_quick_sms(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Message content is required for Quick SMS.');

        Fast2sms::to($this->testNumber)->send();
    }

    // --- DLT SMS Validation ---

    #[Test]
    public function it_throws_an_exception_if_template_id_is_missing_for_dlt(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Template ID is required for DLT.');

        Fast2sms::to($this->testNumber)
            ->route(SmsRoute::DLT)
            ->variables(['var'])
            ->senderId($this->testSenderId)
            ->send();
    }

    #[Test]
    public function it_throws_an_exception_if_variables_are_missing_for_dlt(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Variables values are required for DLT.');

        Fast2sms::to($this->testNumber)
            ->route(SmsRoute::DLT)
            ->templateId($this->testTemplateId)
            ->senderId($this->testSenderId)
            ->send();
    }

    #[Test]
    public function it_throws_an_exception_if_sender_id_is_missing_for_dlt(): void
    {
        config(['fast2sms.default_sender_id' => null]);
        Fast2sms::fake();

        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Sender ID is required for DLT.');

        Fast2sms::to($this->testNumber)
            ->route(SmsRoute::DLT)
            ->templateId($this->testTemplateId)
            ->variables(['var'])
            ->send();
    }

    // --- OTP SMS Validation ---

    #[Test]
    public function it_throws_an_exception_if_otp_value_is_missing(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('OTP value is required for OTP SMS.');

        Fast2sms::to($this->testNumber)->route(SmsRoute::OTP)->send();
    }

    #[Test]
    public function it_throws_an_exception_for_invalid_dlt_manager_type(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Shakil\Fast2sms\Fast2sms::dltManager(): Argument #1 ($type) must be of type Shakil\Fast2sms\Enums\DltManagerType, string given');

        Fast2sms::dltManager('invalid-type');
    }
}
