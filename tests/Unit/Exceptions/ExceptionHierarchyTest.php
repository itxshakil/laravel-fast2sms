<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shakil\Fast2sms\Exceptions\ApiException;
use Shakil\Fast2sms\Exceptions\AuthenticationException;
use Shakil\Fast2sms\Exceptions\ConfigurationException;
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\NetworkException;
use Shakil\Fast2sms\Exceptions\RateLimitException;
use Shakil\Fast2sms\Exceptions\ServerException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Exceptions\ValidationException;

final class ExceptionHierarchyTest extends TestCase
{
    #[Test]
    public function it_configuration_exception_extends_fast2sms_exception(): void
    {
        $e = new ConfigurationException('config error');

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame('config error', $e->getMessage());
    }

    #[Test]
    public function it_validation_exception_extends_fast2sms_exception(): void
    {
        $e = new ValidationException('validation error');

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame('validation error', $e->getMessage());
    }

    #[Test]
    public function it_api_exception_extends_fast2sms_exception(): void
    {
        $e = new ApiException('api error', 400);

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame(400, $e->statusCode);
    }

    #[Test]
    public function it_authentication_exception_extends_api_exception(): void
    {
        $e = new AuthenticationException('unauthorized');

        $this->assertInstanceOf(ApiException::class, $e);
        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame(401, $e->statusCode);
    }

    #[Test]
    public function it_rate_limit_exception_extends_api_exception(): void
    {
        $e = new RateLimitException('too many requests');

        $this->assertInstanceOf(ApiException::class, $e);
        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame(429, $e->statusCode);
    }

    #[Test]
    public function it_server_exception_extends_api_exception(): void
    {
        $e = new ServerException('server error', 503);

        $this->assertInstanceOf(ApiException::class, $e);
        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame(503, $e->statusCode);
    }

    #[Test]
    public function it_network_exception_extends_fast2sms_exception(): void
    {
        $e = new NetworkException('connection failed');

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertSame(0, $e->getCode());
    }

    #[Test]
    public function it_api_exception_from_status_returns_authentication_exception_for_401(): void
    {
        $e = ApiException::fromStatus(401, 'unauthorized', ['error' => 'auth']);

        $this->assertInstanceOf(AuthenticationException::class, $e);
        $this->assertSame(401, $e->statusCode);
        $this->assertSame(['error' => 'auth'], $e->responseBody);
    }

    #[Test]
    public function it_api_exception_from_status_returns_rate_limit_exception_for_429(): void
    {
        $e = ApiException::fromStatus(429, 'too many requests');

        $this->assertInstanceOf(RateLimitException::class, $e);
        $this->assertSame(429, $e->statusCode);
    }

    #[Test]
    public function it_api_exception_from_status_returns_server_exception_for_5xx(): void
    {
        $e = ApiException::fromStatus(503, 'service unavailable');

        $this->assertInstanceOf(ServerException::class, $e);
        $this->assertSame(503, $e->statusCode);
    }

    #[Test]
    public function it_api_exception_from_status_returns_api_exception_for_other_codes(): void
    {
        $e = ApiException::fromStatus(422, 'unprocessable entity');

        $this->assertInstanceOf(ApiException::class, $e);
        $this->assertNotInstanceOf(AuthenticationException::class, $e);
        $this->assertNotInstanceOf(RateLimitException::class, $e);
        $this->assertSame(422, $e->statusCode);
    }

    #[Test]
    public function it_api_exception_carries_response_body(): void
    {
        $body = ['return' => false, 'message' => ['Invalid API key']];
        $e = new ApiException('api error', 400, $body);

        $this->assertSame($body, $e->responseBody);
    }

    #[Test]
    public function it_network_exception_carries_previous_exception(): void
    {
        $previous = new RuntimeException('connection refused');
        $e = new NetworkException('network error', $previous);

        $this->assertSame($previous, $e->getPrevious());
    }

    #[Test]
    public function it_duplicate_send_exception_extends_fast2sms_exception(): void
    {
        $e = DuplicateSendException::detected('abc123');

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertStringContainsString('abc123', $e->getMessage());
    }

    #[Test]
    public function it_throttle_exceeded_exception_extends_fast2sms_exception(): void
    {
        $e = ThrottleExceededException::limitReached(61, 60);

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertStringContainsString('60', $e->getMessage());
    }

    #[Test]
    public function it_insufficient_balance_exception_extends_fast2sms_exception(): void
    {
        $e = InsufficientBalanceException::belowThreshold(5.0, 10.0);

        $this->assertInstanceOf(Fast2smsException::class, $e);
        $this->assertStringContainsString('5', $e->getMessage());
    }
}
