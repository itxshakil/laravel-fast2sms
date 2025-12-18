<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Shakil\Fast2sms\Fast2smsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        // Perform any package-specific setup here
    }

    /**
     * Get package providers.
     *
     * @param  Application              $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            Fast2smsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('fast2sms.api_key', 'test_api_key');
        $app['config']->set('fast2sms.default_sender_id', 'TESTID');
        $app['config']->set('fast2sms.default_route', 'q'); // or 'dlt'
        $app['config']->set('fast2sms.base_url', 'https://www.fast2sms.com/dev');
        $app['config']->set('fast2sms.timeout', 30);
    }
}
