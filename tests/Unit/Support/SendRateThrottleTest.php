<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Support;

use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Support\SendRateThrottle;
use Shakil\Fast2sms\Tests\TestCase;

class SendRateThrottleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('fast2sms:throttle');
    }

    #[Test]
    public function it_allows_send_under_limit(): void
    {
        config(['fast2sms.throttle.max_per_minute' => 5]);

        $throttle = new SendRateThrottle;

        // Should not throw for the first call
        $throttle->check();

        $this->assertSame(1, RateLimiter::attempts('fast2sms:throttle'));
    }

    #[Test]
    public function it_increments_counter_on_each_check(): void
    {
        config(['fast2sms.throttle.max_per_minute' => 10]);

        $throttle = new SendRateThrottle;

        $throttle->check();
        $throttle->check();
        $throttle->check();

        $this->assertEquals(3, RateLimiter::attempts('fast2sms:throttle'));
    }

    #[Test]
    public function it_throws_when_limit_is_reached(): void
    {
        config(['fast2sms.throttle.max_per_minute' => 3]);

        $throttle = new SendRateThrottle;

        $throttle->check();
        $throttle->check();
        $throttle->check();

        $this->expectException(ThrottleExceededException::class);

        $throttle->check();
    }

    #[Test]
    public function it_throws_immediately_when_count_equals_max(): void
    {
        config(['fast2sms.throttle.max_per_minute' => 2]);

        // Pre-fill the rate limiter to the max
        RateLimiter::hit('fast2sms:throttle', 60);
        RateLimiter::hit('fast2sms:throttle', 60);

        $throttle = new SendRateThrottle;

        $this->expectException(ThrottleExceededException::class);

        $throttle->check();
    }

    #[Test]
    public function it_uses_default_max_of_60_when_not_configured(): void
    {
        // Do not set fast2sms.throttle.max_per_minute — let it fall back to the default of 60
        // Pre-fill 59 attempts
        for ($i = 0; $i < 59; $i++) {
            RateLimiter::hit('fast2sms:throttle', 60);
        }

        $throttle = new SendRateThrottle;

        // 59 < 60, should not throw — counter increments to 60
        $throttle->check();

        $this->assertSame(60, RateLimiter::attempts('fast2sms:throttle'));
    }

    #[Test]
    public function it_respects_custom_throttle_store_config(): void
    {
        config([
            'fast2sms.throttle.max_per_minute' => 5,
        ]);

        $throttle = new SendRateThrottle;

        $throttle->check();
        $throttle->check();

        $this->assertEquals(2, RateLimiter::attempts('fast2sms:throttle'));
    }

    #[Test]
    public function it_sets_counter_to_one_on_first_call(): void
    {
        config(['fast2sms.throttle.max_per_minute' => 10]);

        $throttle = new SendRateThrottle;
        $throttle->check();

        $this->assertEquals(1, RateLimiter::attempts('fast2sms:throttle'));
    }
}
