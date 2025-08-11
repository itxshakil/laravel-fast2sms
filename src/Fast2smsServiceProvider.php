<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Support\ServiceProvider;
use Shakil\Fast2sms\Console\Commands\MonitorSmsBalance;

/**
 * Fast2sms Service Provider for Laravel.
 */
class Fast2smsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/fast2sms.php', 'fast2sms'
        );

        $this->app->singleton('fast2sms', function ($app) {
            return new Fast2sms;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorSmsBalance::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/fast2sms.php' => config_path('fast2sms.php'),
        ], 'fast2sms-config');
    }
}
