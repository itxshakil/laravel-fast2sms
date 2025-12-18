<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\DltManagerType;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Tests\TestCase;

class SmsSendingTest extends TestCase
{
    private string $testNumber = '9999999999';

    private string $testSenderId = 'FASTSM';

    private string $testTemplateId = '1234567890123456';

    protected function setUp(): void
    {
        parent::setUp();
        config(['fast2sms.api_key' => 'test-api-key']);
    }

    // --- Happy Path: Quick SMS ---

    #[Test]
    public function it_can_send_a_quick_sms_with_the_fluent_api(): void
    {
        $this->mockSuccessfulSmsResponse();

        $response = Fast2sms::to($this->testNumber)
            ->message('Test Quick SMS')
            ->send();

        $this->assertInstanceOf(SmsResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertNotNull($response->getRequestId());

        Http::assertSent(function ($request) {
            $data = collect($request->data())->mapWithKeys(function ($item) {
                return [$item['name'] => $item['contents']];
            })->all();

            return $data['route'] === SmsRoute::QUICK->value
                && $data['numbers'] === $this->testNumber
                && $data['message'] === 'Test Quick SMS';
        });
    }

    #[Test]
    public function it_can_send_a_quick_sms_with_the_quick_method(): void
    {
        $this->mockSuccessfulSmsResponse();

        $response = Fast2sms::quick($this->testNumber, 'Test Quick SMS');

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            $data = collect($request->data())->mapWithKeys(function ($item) {
                return [$item['name'] => $item['contents']];
            })->all();

            return $data['route'] === SmsRoute::QUICK->value
                && $data['numbers'] === $this->testNumber
                && $data['message'] === 'Test Quick SMS';
        });
    }

    // --- Happy Path: DLT SMS ---

    #[Test]
    public function it_can_send_a_dlt_sms_with_the_fluent_api(): void
    {
        $this->mockSuccessfulSmsResponse();

        $response = Fast2sms::to($this->testNumber)
            ->route(SmsRoute::DLT)
            ->senderId($this->testSenderId)
            ->templateId($this->testTemplateId)
            ->variables(['Hello', 'World'])
            ->send();

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            $data = collect($request->data())->mapWithKeys(function ($item) {
                return [$item['name'] => $item['contents']];
            })->all();

            return $data['route'] === SmsRoute::DLT->value
                && $data['template_id'] === $this->testTemplateId
                && $data['variables_values'] === 'Hello|World'
                && $data['sender_id'] === $this->testSenderId;
        });
    }

    #[Test]
    public function it_can_send_a_dlt_sms_with_the_dlt_method(): void
    {
        $this->mockSuccessfulSmsResponse();

        $response = Fast2sms::dlt(
            $this->testNumber,
            $this->testTemplateId,
            ['Hello', 'World'],
            $this->testSenderId,
        );

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            $data = collect($request->data())->mapWithKeys(function ($item) {
                return [$item['name'] => $item['contents']];
            })->all();

            return $data['route'] === SmsRoute::DLT->value
                && $data['template_id'] === $this->testTemplateId
                && $data['variables_values'] === 'Hello|World'
                && $data['sender_id'] === $this->testSenderId;
        });
    }

    // --- Happy Path: OTP SMS ---

    #[Test]
    public function it_can_send_an_otp_sms_with_the_otp_method(): void
    {
        $this->mockSuccessfulSmsResponse();

        $response = Fast2sms::otp($this->testNumber, '123456');

        $this->assertTrue($response->isSuccess());

        Http::assertSent(function ($request) {
            $data = collect($request->data())->mapWithKeys(function ($item) {
                return [$item['name'] => $item['contents']];
            })->all();

            return $data['route'] === SmsRoute::OTP->value
                && $data['numbers'] === $this->testNumber
                && $data['variables_values'] === '123456';
        });
    }

    // --- Other Functionality ---

    #[Test]
    public function it_can_check_the_wallet_balance(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/wallet' => Http::response(['return' => true, 'wallet' => '500.50', 'sms_count' => 1000]),
        ]);

        $response = Fast2sms::checkBalance();

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(500.50, $response->balance);
        $this->assertEquals(1000, $response->smsCount);
    }

    #[Test]
    public function it_can_retrieve_dlt_manager_senders(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/dlt_manager*' => Http::response([
                'success' => true,
                'data' => [
                    ['sender_id' => 'SENDER1', 'entity_id' => '1', 'entity_name' => 'Name 1'],
                    ['sender_id' => 'SENDER2', 'entity_id' => '2', 'entity_name' => 'Name 2'],
                ],
            ]),
        ]);

        $response = Fast2sms::dltManager(DltManagerType::SENDER);
        $senders = $response->getSenders();

        $this->assertTrue($response->isSuccess());
        $this->assertCount(2, $senders);
        $this->assertEquals('SENDER1', $senders[0]['sender_id']);
    }

    #[Test]
    public function it_can_retrieve_dlt_manager_templates(): void
    {
        Http::fake([
            config('fast2sms.base_url') . '/dlt_manager*' => Http::response([
                'success' => true,
                'data' => [
                    ['templates' => [['template_id' => 'TPL1'], ['template_id' => 'TPL2']]],
                ],
            ]),
        ]);

        $response = Fast2sms::dltManager(DltManagerType::TEMPLATE);
        $templates = $response->getTemplates();

        $this->assertTrue($response->isSuccess());
        $this->assertCount(2, $templates);
        $this->assertEquals('TPL1', $templates[0]['template_id']);
    }

    /**
     * Helper to mock a successful SMS send response.
     */
    private function mockSuccessfulSmsResponse(array $extraData = []): void
    {
        Http::fake([
            config('fast2sms.base_url') . '*' => Http::response(array_merge([
                'return' => true,
                'message' => 'SMS sent successfully.',
                'request_id' => 'xyz-123',
            ], $extraData)),
        ]);
    }
}
