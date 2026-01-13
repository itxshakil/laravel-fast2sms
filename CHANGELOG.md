# Changelog

All notable changes to `laravel-fast2sms` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Observability & Logging**:
    - Optional database-backed logging system with `fast2sms_logs` table.
    - New `LogSmsSent` and `LogSmsFailed` event listeners.
    - `log` driver for local development to prevent credit wastage.
- **Resilience**:
    - Automatic retries using Laravel's `Http::retry()` (3 attempts, 100ms backoff).
    - Config validation during boot with clear `Fast2smsException`.
- **Developer Experience (DX)**:
    - Enhanced `SmsChannel` to support `SmsMessage` objects with recipient data.
    - Fluent `to()` and `send()` methods in `SmsMessage`.
    - Support for Laravel `Collection` in `to()` method.
    - Custom `Fast2smsPhone` validation rule for Indian mobile numbers.
    - Improved `Fast2smsResponse` with dynamic property access and `json()` method.
- **Testing**:
    - Added comprehensive tests for database logging, log driver, retries, config validation, and the new validation rule.

## [1.2.0] - 2025-12-18

### Added
- PHP 8.5 support

## [1.1.0] - 2025-08-19

### Added
- PHP 8.4 support

## [1.0.0] - 2025-08-16

### Added
- Initial release of Laravel Fast2SMS integration
- Fast2SMS service provider for Laravel
- Configuration file for Fast2SMS credentials and settings
- Notification channel support for Laravel
- Console command for monitoring SMS balance
- Support for sending single and bulk SMS
- Event system for SMS status tracking
- Data Transfer Objects for SMS messages
- Facade for easy access to Fast2SMS services
- Comprehensive exception handling
- Response handling and parsing
- PHPUnit test suite
- Laravel 12.x compatibility
- PHP 8.3+ support

### Security
- Secure handling of API credentials
- Input validation and sanitization
- Rate limiting support

[1.1.0]: https://github.com/itxshakil/laravel-fast2sms/releases/tag/v1.1.0
[1.0.0]: https://github.com/itxshakil/laravel-fast2sms/releases/tag/v1.0.0
