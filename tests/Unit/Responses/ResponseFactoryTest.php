<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Responses;

use Illuminate\Support\Facades\Event;
use Shakil\Fast2sms\Enums\ResponseType;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Responses\DltManagerResponse;
use Shakil\Fast2sms\Responses\Fast2smsResponse;
use Shakil\Fast2sms\Responses\ResponseFactory;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Tests\TestCase;

class ResponseFactoryTest extends TestCase
{
    public function test_it_returns_wallet_balance_response_for_wallet_type(): void
    {
        $response = ResponseFactory::make(
            [],
            ['return' => true, 'wallet' => '150.00', 'message' => 'ok'],
            ResponseType::WalletBalance,
        );

        $this->assertInstanceOf(WalletBalanceResponse::class, $response);
    }

    public function test_it_returns_sms_response_for_sms_type(): void
    {
        $response = ResponseFactory::make(
            [],
            ['return' => true, 'request_id' => 'req123', 'message' => 'sent'],
            ResponseType::Sms,
        );

        $this->assertInstanceOf(SmsResponse::class, $response);
    }

    public function test_it_returns_dlt_manager_response_for_dlt_manager_type(): void
    {
        $response = ResponseFactory::make(
            [],
            ['success' => true, 'data' => [['id' => 1]], 'message' => 'ok'],
            ResponseType::DltManager,
        );

        $this->assertInstanceOf(DltManagerResponse::class, $response);
    }

    public function test_it_returns_whatsapp_response_for_whatsapp_type(): void
    {
        $response = ResponseFactory::make(
            [],
            ['success' => true, 'message' => 'sent'],
            ResponseType::WhatsApp,
        );

        $this->assertInstanceOf(WhatsAppResponse::class, $response);
    }

    public function test_it_dispatches_sms_sent_event_when_making_sms_response(): void
    {
        Event::fake();

        ResponseFactory::make(
            ['numbers' => '9999999999'],
            ['return' => true, 'request_id' => 'req456', 'message' => 'sent'],
            ResponseType::Sms,
        );

        Event::assertDispatched(SmsSent::class);
    }

    public function test_it_returns_wallet_balance_response_via_fallback_when_wallet_key_present(): void
    {
        $response = ResponseFactory::make(
            [],
            ['return' => true, 'wallet' => '200.00', 'message' => 'ok'],
        );

        $this->assertInstanceOf(WalletBalanceResponse::class, $response);
    }

    public function test_it_returns_sms_response_via_fallback_when_request_id_present(): void
    {
        $response = ResponseFactory::make(
            [],
            ['return' => true, 'request_id' => 'req789', 'message' => 'sent'],
        );

        $this->assertInstanceOf(SmsResponse::class, $response);
    }

    public function test_it_returns_dlt_manager_response_via_fallback_when_success_and_data_present(): void
    {
        $response = ResponseFactory::make(
            [],
            ['success' => true, 'data' => [['id' => 2]], 'message' => 'ok'],
        );

        $this->assertInstanceOf(DltManagerResponse::class, $response);
    }

    public function test_it_returns_generic_response_as_fallback(): void
    {
        $response = ResponseFactory::make(
            [],
            ['return' => true, 'message' => 'generic'],
        );

        $this->assertInstanceOf(Fast2smsResponse::class, $response);
        $this->assertNotInstanceOf(SmsResponse::class, $response);
        $this->assertNotInstanceOf(WalletBalanceResponse::class, $response);
        $this->assertNotInstanceOf(DltManagerResponse::class, $response);
    }
}
