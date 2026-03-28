<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\DataTransferObjects;

use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\DataTransferObjects\Fast2smsConfig;
use Shakil\Fast2sms\Enums\SmsRoute;

class Fast2smsConfigTest extends TestCase
{
    public function test_it_creates_config_from_array_with_all_keys(): void
    {
        $config = Fast2smsConfig::fromArray([
            'api_key' => 'my_key',
            'base_url' => 'https://api.example.com',
            'driver' => 'api',
            'timeout' => 60,
            'default_route' => 'dlt',
            'default_sender_id' => 'SENDER',
            'database_logging' => true,
            'whatsapp' => [
                'default_phone_number_id' => 'PHONE123',
                'default_waba_id' => 'WABA456',
                'version' => 'v25.0',
            ],
        ]);

        $this->assertSame('my_key', $config->apiKey);
        $this->assertSame('https://api.example.com', $config->baseUrl);
        $this->assertSame('api', $config->driver);
        $this->assertSame(60, $config->timeout);
        $this->assertSame(SmsRoute::DLT, $config->defaultRoute);
        $this->assertSame('SENDER', $config->defaultSenderId);
        $this->assertSame('PHONE123', $config->whatsappPhoneNumberId);
        $this->assertSame('WABA456', $config->whatsappWabaId);
        $this->assertSame('v25.0', $config->whatsappVersion);
    }

    public function test_it_uses_defaults_for_missing_keys(): void
    {
        $config = Fast2smsConfig::fromArray([]);

        $this->assertSame('', $config->apiKey);
        $this->assertSame('', $config->baseUrl);
        $this->assertSame('api', $config->driver);
        $this->assertSame(30, $config->timeout);
        $this->assertSame(SmsRoute::QUICK, $config->defaultRoute);
        $this->assertNull($config->defaultSenderId);
        $this->assertSame('', $config->whatsappPhoneNumberId);
        $this->assertSame('', $config->whatsappWabaId);
        $this->assertSame('v24.0', $config->whatsappVersion);
    }

    public function test_it_casts_timeout_to_int(): void
    {
        $config = Fast2smsConfig::fromArray(['timeout' => '45']);

        $this->assertSame(45, $config->timeout);
        $this->assertIsInt($config->timeout);
    }

    public function test_it_resolves_default_route_enum(): void
    {
        $config = Fast2smsConfig::fromArray(['default_route' => 'otp']);

        $this->assertSame(SmsRoute::OTP, $config->defaultRoute);
    }

    public function test_it_ignores_database_logging_key(): void
    {
        $config = Fast2smsConfig::fromArray(['database_logging' => 1]);

        $this->assertSame('', $config->whatsappPhoneNumberId);
    }
}
