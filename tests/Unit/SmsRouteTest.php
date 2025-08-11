<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Tests\TestCase;
use ValueError;

/**
 * Unit tests for the SmsRoute Enum.
 */
class SmsRouteTest extends TestCase
{
    #[Test]
    public function sms_route_enums_have_correct_values(): void
    {
        $this->assertEquals('dlt_manual', SmsRoute::DLT_MANUAL->value);
        $this->assertEquals('dlt', SmsRoute::DLT->value);
        $this->assertEquals('otp', SmsRoute::OTP->value);
        $this->assertEquals('q', SmsRoute::QUICK->value);
    }

    #[Test]
    public function sms_route_can_be_created_from_string(): void
    {
        $this->assertEquals(SmsRoute::DLT, SmsRoute::from('dlt'));
        $this->assertEquals(SmsRoute::OTP, SmsRoute::from('otp'));
    }

    #[Test]
    public function invalid_sms_route_string_throws_error(): void
    {
        $this->expectException(ValueError::class); // Enum::from() throws ValueError for invalid cases
        SmsRoute::from('invalid_route');
    }
}
