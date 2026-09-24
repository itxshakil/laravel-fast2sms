<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Testing;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Enums\DeliveryStatus;
use Shakil\Fast2sms\Responses\DeliveryStatusResponse;
use Shakil\Fast2sms\Testing\Fast2smsFake;
use Shakil\Fast2sms\Testing\RecordedWebhook;

final class Fast2smsFakeWebhookTest extends TestCase
{
    private Fast2smsFake $fake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fake = new Fast2smsFake();
    }

    #[Test]
    public function it_starts_with_no_handled_webhooks(): void
    {
        $this->assertSame([], $this->fake->handledWebhooks());
        $this->fake->assertWebhookNotHandled();
        $this->fake->assertWebhookHandledCount(0);
    }

    #[Test]
    public function it_records_handled_webhooks_as_typed_objects(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));
        $this->fake->recordWebhook($this->failed('req_2'));

        $handled = $this->fake->handledWebhooks();

        $this->assertCount(2, $handled);
        $this->assertInstanceOf(RecordedWebhook::class, $handled[0]);
        $this->assertSame('req_1', $handled[0]->response->requestId);
        $this->assertSame(DeliveryStatus::Failed, $handled[1]->response->status);
    }

    #[Test]
    public function assert_webhook_handled_passes_with_and_without_closure(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));

        $this->fake->assertWebhookHandled();
        $this->fake->assertWebhookHandled(fn (DeliveryStatusResponse $r): bool => $r->requestId === 'req_1');
    }

    #[Test]
    public function assert_webhook_handled_fails_when_nothing_matches(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWebhookHandled(fn (DeliveryStatusResponse $r): bool => $r->requestId === 'other');
    }

    #[Test]
    public function assert_webhook_not_handled_fails_when_one_was_handled(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWebhookNotHandled();
    }

    #[Test]
    public function assert_webhook_handled_count_checks_exact_count(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));
        $this->fake->recordWebhook($this->delivered('req_2'));

        $this->fake->assertWebhookHandledCount(2);

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertWebhookHandledCount(1);
    }

    #[Test]
    public function assert_message_delivered_matches_status_and_optional_request_id(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));
        $this->fake->recordWebhook($this->failed('req_2'));

        $this->fake->assertMessageDelivered();
        $this->fake->assertMessageDelivered('req_1');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertMessageDelivered('req_2');
    }

    #[Test]
    public function assert_message_failed_matches_status_and_optional_request_id(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));
        $this->fake->recordWebhook($this->failed('req_2'));

        $this->fake->assertMessageFailed();
        $this->fake->assertMessageFailed('req_2');

        $this->expectException(AssertionFailedError::class);

        $this->fake->assertMessageFailed('req_1');
    }

    #[Test]
    public function reset_clears_handled_webhooks(): void
    {
        $this->fake->recordWebhook($this->delivered('req_1'));

        $this->fake->reset();

        $this->fake->assertWebhookNotHandled();
    }

    private function delivered(string $requestId): DeliveryStatusResponse
    {
        return DeliveryStatusResponse::fromPayload(['request_id' => $requestId, 'status' => 'delivered']);
    }

    private function failed(string $requestId): DeliveryStatusResponse
    {
        return DeliveryStatusResponse::fromPayload(['request_id' => $requestId, 'status' => 'failed', 'failure_reason' => 'DND']);
    }
}
