<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Support\ConfigValidator;

class ConfigValidatorTest extends TestCase
{
    public function test_it_passes_when_config_is_valid(): void
    {
        $this->expectNotToPerformAssertions();

        ConfigValidator::validate([
            'base_url' => 'https://www.fast2sms.com/dev',
            'driver' => 'api',
            'api_key' => 'my_api_key',
        ]);
    }

    public function test_it_throws_when_base_url_is_missing(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Fast2sms base_url is not configured.');

        ConfigValidator::validate([
            'base_url' => '',
            'driver' => 'api',
            'api_key' => 'my_api_key',
        ]);
    }

    public function test_it_throws_when_api_key_is_missing_for_api_driver(): void
    {
        $this->expectException(Fast2smsException::class);
        $this->expectExceptionMessage('Fast2sms API Key is not configured.');

        ConfigValidator::validate([
            'base_url' => 'https://www.fast2sms.com/dev',
            'driver' => 'api',
            'api_key' => '',
        ]);
    }

    public function test_it_passes_when_driver_is_log_and_api_key_is_missing(): void
    {
        $this->expectNotToPerformAssertions();

        ConfigValidator::validate([
            'base_url' => 'https://www.fast2sms.com/dev',
            'driver' => 'log',
            'api_key' => '',
        ]);
    }

    public function test_it_passes_when_driver_is_log_and_api_key_is_absent(): void
    {
        $this->expectNotToPerformAssertions();

        ConfigValidator::validate([
            'base_url' => 'https://www.fast2sms.com/dev',
            'driver' => 'log',
        ]);
    }
}
