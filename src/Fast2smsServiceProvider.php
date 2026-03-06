<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use Override;
use Shakil\Fast2sms\Channels\SmsChannel;
use Shakil\Fast2sms\Channels\WhatsAppChannel;
use Shakil\Fast2sms\Clients\HttpClient;
use Shakil\Fast2sms\Clients\LogClient;
use Shakil\Fast2sms\Console\Commands\GenerateIdeHelper;
use Shakil\Fast2sms\Console\Commands\ListEvents;
use Shakil\Fast2sms\Console\Commands\MonitorSmsBalance;
use Shakil\Fast2sms\Console\Commands\WhatsAppWabaDetails;
use Shakil\Fast2sms\Contracts\ClientInterface;
use Shakil\Fast2sms\DataTransferObjects\Fast2smsConfig;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Support\ConfigValidator;

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

        $this->app->register(Fast2smsEventServiceProvider::class);

        $this->app->singleton(ClientInterface::class, function ($app): LogClient|HttpClient {
            $config = $app['config']['fast2sms'];

            if ($config['driver'] === 'log') {
                return new LogClient();
            }

            return new HttpClient(
                apiKey: $config['api_key'] ?? '',
                baseUrl: $config['base_url'] ?? '',
                timeout: (int) ($config['timeout'] ?? 30),
            );
        });

        $this->app->singleton('fast2sms', fn ($app): Fast2sms => new Fast2sms(
            $app->make(ClientInterface::class),
            Fast2smsConfig::fromArray($app['config']['fast2sms']),
        ));

        $this->app->singleton('fast2sms.whatsapp', fn ($app): WhatsApp => new WhatsApp(
            $app->make(ClientInterface::class),
            Fast2smsConfig::fromArray($app['config']['fast2sms']),
        ));

        $this->app->extend(ChannelManager::class, function ($service, $app) {
            $service->extend('fast2sms', fn ($app) => $app->make(SmsChannel::class));
            $service->extend('whatsapp', fn ($app) => $app->make(WhatsAppChannel::class));

            return $service;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @throws Fast2smsException
     */
    public function boot(): void
    {
        if (! $this->app->runningUnitTests()) {
            ConfigValidator::validate($this->app['config']['fast2sms']);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListEvents::class,
                MonitorSmsBalance::class,
                WhatsAppWabaDetails::class,
            ]);

            if (! $this->app->isProduction()) {
                $this->commands([
                    GenerateIdeHelper::class,
                ]);
            }

            $this->loadMigrations();
        }

        $this->publishes([
            __DIR__ . '/../config/fast2sms.php' => config_path('fast2sms.php'),
        ], 'fast2sms-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'fast2sms-migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    #[Override]
    public function provides(): array
    {
        return ['fast2sms', 'fast2sms.whatsapp'];
    }

    /**
     * Load the migrations.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
