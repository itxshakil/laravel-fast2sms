<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Clients;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Shakil\Fast2sms\Clients\HttpClient;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Exceptions\ApiException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\NetworkException;
use Shakil\Fast2sms\Tests\TestCase;

class HttpClientTest extends TestCase
{
    private HttpClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new HttpClient(
            apiKey: 'test_key',
            baseUrl: 'https://www.fast2sms.com/dev',
            timeout: 30,
        );
    }

    public function test_it_returns_response_on_successful_post(): void
    {
        Http::fake([
            '*' => Http::response(['return' => true, 'request_id' => 'abc123', 'message' => 'sent'], 200),
        ]);

        $response = $this->client->post('v1/send', ['numbers' => '9999999999']);

        $this->assertTrue($response->isSuccess());
    }

    public function test_it_throws_on_http_error_for_post(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Fast2sms API Error: Unauthorized');

        $this->client->post('v1/send', ['numbers' => '9999999999']);
    }

    public function test_it_wraps_network_exception_for_post(): void
    {
        Http::fake(fn () => throw new RuntimeException('Connection refused'));

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Fast2sms request failed: Connection refused');

        $this->client->post('v1/send', ['numbers' => '9999999999']);
    }

    public function test_it_returns_response_on_successful_get(): void
    {
        Http::fake([
            '*' => Http::response(['return' => true, 'wallet' => '100.00'], 200),
        ]);

        $response = $this->client->get('v1/wallet');

        $this->assertTrue($response->isSuccess());
    }

    public function test_it_throws_on_http_error_for_get(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Not Found'], 404),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Fast2sms API Error: Not Found');

        $this->client->get('v1/wallet');
    }

    public function test_it_wraps_network_exception_for_get(): void
    {
        Http::fake(fn () => throw new RuntimeException('Timeout'));

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Fast2sms request failed: Timeout');

        $this->client->get('v1/wallet');
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function test_it_returns_response_on_successful_delete(): void
    {
        Http::fake([
            '*' => Http::response(['return' => true, 'message' => 'deleted'], 200),
        ]);

        $response = $this->client->delete('v1/media/123');

        $this->assertTrue($response->isSuccess());
    }

    public function test_it_throws_on_http_error_for_delete(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Fast2sms API Error: Forbidden');

        $this->client->delete('v1/media/123');
    }

    public function test_it_wraps_network_exception_for_delete(): void
    {
        Http::fake(fn () => throw new RuntimeException('Network error'));

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Fast2sms request failed: Network error');

        $this->client->delete('v1/media/123');
    }

    public function test_it_does_not_dispatch_sms_failed_event_on_http_error(): void
    {
        Event::fake();

        Http::fake([
            '*' => Http::response(['message' => 'Server Error'], 500),
        ]);

        try {
            $this->client->post('v1/send', ['numbers' => '9999999999']);
        } catch (Fast2smsException) {
            // expected — event dispatching is handled at the service layer
        }

        Event::assertNotDispatched(SmsFailed::class);
    }

    public function test_it_does_not_dispatch_sms_failed_event_on_network_exception(): void
    {
        Event::fake();

        Http::fake(fn () => throw new RuntimeException('DNS failure'));

        try {
            $this->client->post('v1/send', ['numbers' => '9999999999']);
        } catch (Fast2smsException) {
            // expected — event dispatching is handled at the service layer
        }

        Event::assertNotDispatched(SmsFailed::class);
    }
}
