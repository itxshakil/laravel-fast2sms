<?php
declare(strict_types=1);

namespace Tests\Unit\Notifications\Messages;

use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Notifications\Messages\SmsMessage;
use Shakil\Fast2sms\Tests\TestCase;

class SmsMessageTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Fast2sms::fake();
    }

    public function test_it_can_set_content()
    {
        $message = new SmsMessage();
        $message->content('Test message');

        $this->assertEquals('Test message', $message->content);
    }

    public function test_it_can_be_created_with_content()
    {
        $message = new SmsMessage('Test message');

        $this->assertEquals('Test message', $message->content);
    }

    public function test_it_can_set_template()
    {
        $message = new SmsMessage();
        $message->template('template123', ['var1', 'var2']);

        $this->assertEquals('template123', $message->templateId);
        $this->assertEquals(['var1', 'var2'], $message->variables);
//        $this->assertEquals(SmsRoute::QUICK, $message->route);
    }

    public function test_it_can_set_sender_id()
    {
        $message = new SmsMessage();
        $message->from('TESTID');

        $this->assertEquals('TESTID', $message->senderId);
    }

    public function test_it_can_set_route()
    {
        $message = new SmsMessage();
        $message->route(SmsRoute::QUICK);

        $this->assertEquals(SmsRoute::QUICK, $message->route);
    }

    public function test_it_can_set_language()
    {
        $message = new SmsMessage();
        $message->language(SmsLanguage::UNICODE);

        $this->assertEquals(SmsLanguage::UNICODE, $message->language);
    }

    public function test_it_can_chain_methods()
    {
        $message = (new SmsMessage())
            ->content('Test message')
            ->from('TESTID')
            ->route(SmsRoute::QUICK)
            ->language(SmsLanguage::UNICODE);

        $this->assertEquals('Test message', $message->content);
        $this->assertEquals('TESTID', $message->senderId);
        $this->assertEquals(SmsRoute::QUICK, $message->route);
        $this->assertEquals(SmsLanguage::UNICODE, $message->language);
    }

    public function test_it_can_create_dlt_message()
    {
        $message = (new SmsMessage())
            ->template('template123', ['var1', 'var2'])
            ->from('TESTID');

        $this->assertEquals('template123', $message->templateId);
        $this->assertEquals(['var1', 'var2'], $message->variables);
        $this->assertEquals('TESTID', $message->senderId);
//        $this->assertEquals(SmsRoute::DLT, $message->route);
    }
}
