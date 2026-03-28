<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Feature;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Clients\LogClient;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;
use Shakil\Fast2sms\Tests\TestCase;

class LogClientTest extends TestCase
{
    private LogClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new LogClient();
    }

    #[Test]
    public function it_post_returns_response_interface(): void
    {
        Log::shouldReceive('info')->once();

        $response = $this->client->post('/bulkV2', ['numbers' => '9876543210', 'route' => 'q']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_post_returns_sms_response_for_bulk_path(): void
    {
        Log::shouldReceive('info')->once();

        $response = $this->client->post('/bulkV2', ['numbers' => '9876543210', 'route' => 'q']);

        $this->assertInstanceOf(SmsResponse::class, $response);
    }

    #[Test]
    public function it_get_returns_response_interface(): void
    {
        Log::shouldReceive('info')->once();

        $response = $this->client->get('/wallet');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertTrue($response->isSuccess());
    }

    #[Test]
    public function it_get_wallet_returns_wallet_balance_response(): void
    {
        Log::shouldReceive('info')->once();

        $response = $this->client->get('/wallet');

        $this->assertInstanceOf(WalletBalanceResponse::class, $response);
        $this->assertSame(1000.0, $response->balance);
    }

    #[Test]
    public function it_delete_returns_response_interface(): void
    {
        Log::shouldReceive('info')->once();

        $response = $this->client->delete('/whatsapp/v24.0/123456/block_users', ['block_users' => []]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    #[Test]
    public function it_upload_returns_response_interface(): void
    {
        Log::shouldReceive('info')->once();

        $response = $this->client->upload('/whatsapp/v24.0/123456/media', '/tmp/test.jpg', ['type' => 'image/jpeg']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    #[Test]
    public function it_post_logs_path_and_payload(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $payload): bool {
                return str_contains($message, '/bulkV2')
                    && isset($payload['numbers']);
            });

        $this->client->post('/bulkV2', ['numbers' => '9876543210', 'route' => 'q']);
    }
}
