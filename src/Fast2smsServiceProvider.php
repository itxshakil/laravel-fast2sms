<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Support\ServiceProvider;
use Override;
use Shakil\Fast2sms\Console\Commands\MonitorSmsBalance;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Listeners\LogSmsFailed;
use Shakil\Fast2sms\Listeners\LogSmsSent;

/**
 * Fast2sms Service Provider for Laravel.
 */
class Fast2smsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fast2sms.php',
            'fast2sms',
        );

        $this->app->singleton('fast2sms', fn ($app): Fast2sms => new Fast2sms);
    }

    /**
     * Bootstrap any application services.
     * @throws Fast2smsException
     */
    public function boot(): void
    {
        $this->validateConfig();

        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorSmsBalance::class,
            ]);

            $this->loadMigrations();
        }

        $this->publishes([
            __DIR__ . '/../config/fast2sms.php' => config_path('fast2sms.php'),
        ], 'fast2sms-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'fast2sms-migrations');

        $this->registerEventListeners();
    }

    /**
     * Register the event listeners.
     */
    protected function registerEventListeners(): void
    {
        $this->app['events']->listen(SmsSent::class, LogSmsSent::class);
        $this->app['events']->listen(SmsFailed::class, LogSmsFailed::class);
    }

    /**
     * Load the migrations.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Validate the package configuration.
     * @throws Fast2smsException
     */
    protected function validateConfig(): void
    {
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            return;
        }

        $config = $this->app['config']['fast2sms'];

        if (empty($config['base_url'])) {
            throw new Fast2smsException('Fast2sms base_url is not configured.');
        }

        if ($config['driver'] === 'api' && empty($config['api_key'])) {
            throw new Fast2smsException('Fast2sms API Key is not configured. Please set FAST2SMS_API_KEY in your .env file.');
        }
    }
}
