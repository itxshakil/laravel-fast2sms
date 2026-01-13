<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Rules;

use Shakil\Fast2sms\Rules\Fast2smsPhone;
use Shakil\Fast2sms\Tests\TestCase;

class Fast2smsPhoneTest extends TestCase
{
    public function test_it_validates_indian_mobile_numbers(): void
    {
        $rule = new Fast2smsPhone();

        $fail = function ($message) {
            $this->fail("Validation failed: $message");
        };

        // Valid numbers
        $rule->validate('phone', '9999999999', $fail);
        $rule->validate('phone', '8888888888', $fail);
        $rule->validate('phone', '7777777777', $fail);
        $rule->validate('phone', '6666666666', $fail);

        // Multiple valid numbers
        $rule->validate('phone', '9999999999,8888888888', $fail);
        $rule->validate('phone', '9999999999, 8888888888', $fail);

        $this->assertTrue(true);
    }

    public function test_it_fails_invalid_numbers(): void
    {
        $rule = new Fast2smsPhone();

        $failed = false;
        $fail = function ($message) use (&$failed) {
            $failed = true;
            $this->assertEquals('The :attribute must be a valid 10-digit Indian mobile number.', $message);
        };

        // Invalid numbers
        $rule->validate('phone', '1234567890', $fail);
        $this->assertTrue($failed);
        $failed = false;

        $rule->validate('phone', '999999999', $fail); // 9 digits
        $this->assertTrue($failed);
        $failed = false;

        $rule->validate('phone', '99999999999', $fail); // 11 digits
        $this->assertTrue($failed);
        $failed = false;

        $rule->validate('phone', 'abcdefghij', $fail);
        $this->assertTrue($failed);
        $failed = false;

        // Mixed valid and invalid
        $rule->validate('phone', '9999999999,12345', $fail);
        $this->assertTrue($failed);
    }
}
