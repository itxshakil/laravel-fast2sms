<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Support;

use function in_array;

use function is_array;

use function is_int;

use function is_string;

use Shakil\Fast2sms\Exceptions\ConfigurationException;

use function sprintf;

/**
 * Validates the fast2sms package configuration.
 *
 * Extracted from Fast2smsServiceProvider to keep the provider focused
 * on container bindings and asset publishing.
 */
final class ConfigValidator
{
    private const array VALID_DRIVERS = ['api', 'log'];

    /**
     * Validate the given configuration array.
     *
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationException
     */
    public static function validate(array $config): void
    {
        self::validateBaseUrl($config);
        self::validateDriver($config);
        self::validateApiKey($config);
        self::validateTimeout($config);
        self::validateWhatsApp($config);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationException
     */
    private static function validateBaseUrl(array $config): void
    {
        if (empty($config['base_url'])) {
            throw new ConfigurationException('Fast2sms base_url is not configured.');
        }

        if (! filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
            throw new ConfigurationException(
                sprintf('Fast2sms base_url "%s" is not a valid URL.', $config['base_url']),
            );
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationException
     */
    private static function validateDriver(array $config): void
    {
        $driver = $config['driver'] ?? null;

        if (! is_string($driver) || ! in_array($driver, self::VALID_DRIVERS, strict: true)) {
            throw new ConfigurationException(
                sprintf(
                    'Fast2sms driver "%s" is invalid. Allowed values: %s.',
                    $driver ?? 'null',
                    implode(', ', self::VALID_DRIVERS),
                ),
            );
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationException
     */
    private static function validateApiKey(array $config): void
    {
        if (($config['driver'] ?? null) === 'api' && empty($config['api_key'])) {
            throw new ConfigurationException(
                'Fast2sms API Key is not configured. Please set FAST2SMS_API_KEY in your .env file.',
            );
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationException
     */
    private static function validateTimeout(array $config): void
    {
        $timeout = $config['timeout'] ?? null;

        if ($timeout !== null && (! is_int($timeout) || $timeout <= 0)) {
            throw new ConfigurationException(
                sprintf('Fast2sms timeout must be a positive integer, got "%s".', $timeout),
            );
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationException
     */
    private static function validateWhatsApp(array $config): void
    {
        $whatsapp = $config['whatsapp'] ?? [];

        if (! is_array($whatsapp)) {
            throw new ConfigurationException('Fast2sms whatsapp configuration must be an array.');
        }

        $version = $whatsapp['version'] ?? null;

        if ($version !== null && ! is_string($version)) {
            throw new ConfigurationException('Fast2sms whatsapp.version must be a string.');
        }
    }
}
