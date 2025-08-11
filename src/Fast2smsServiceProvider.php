<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Support\ServiceProvider;

/**
 * Fast2sms Service Provider for Laravel.
 *
 * @package Shakil\Fast2sms
 */
class Fast2smsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fast2sms.php', 'fast2sms'
        );

        $this->app->singleton('fast2sms', function ($app) {
            return new Fast2sms();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/fast2sms.php' => config_path('fast2sms.php'),
        ], 'fast2sms-config');
    }
}

