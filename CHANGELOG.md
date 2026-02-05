# Changelog

All notable changes to `laravel-fast2sms` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2026-01-17

### Added
- **Laravel Notification Support**:
    - Dedicated `SmsChannel` and `WhatsAppChannel` for Laravel notifications.
    - `WhatsAppMessage` builder for building expressive WhatsApp notification messages.
    - Updated `SmsMessage` builder for better integration with the notification system.
    - Automatic registration of `fast2sms` and `whatsapp` notification drivers.

## [2.1.0] - 2026-01-17

### Added
- **Console Commands**:
    - `whatsapp:waba`: Fetch and display WhatsApp WABA and Template details in a table format.
- **Advanced WhatsApp Features**:
    - **Interactive Messages**: Support for sending list, buttons, and catalog messages via `sendInteractive()`.
    - **Location Messaging**: Support for sending geolocation data via `sendLocation()`.
    - **Reactions**: Support for sending emoji reactions to messages via `sendReaction()`.
    - **User Blocking**: Manage blocked users with `block()`, `unblock()`, and `getBlockedUsers()`.
    - **Analytics & Logs**: Retrieve delivery reports, logs, and summaries via `getDeliveryReport()`, `getLogs()`, and `getSummary()`.
    - **Business Management**: Manage business profiles and phone number details via `getBusinessProfile()`, `updateBusinessProfile()`, `getPhoneNumbers()`, and `getPhoneNumberDetails()`.
    - **Media Management**: Upload media files directly using `uploadMedia()`.
    - **Component Support**: Added `components()` method for full control over Meta template payloads.
- **Improved DX**:
    - Added support for `STICKER` message type.
    - Enhanced fluent builder with `components()` support.
    - Updated `Fast2sms` facade with new WhatsApp methods for better IDE autocomplete.

## [2.0.0] - 2026-01-17

### Added
- **WhatsApp Support**:
    - Complete integration with Fast2SMS WhatsApp API.
    - Simplified and Meta (Advanced) session messaging support.
    - Template-based messaging with variable support.
    - WABA and Template management API support.
- **Improved DX**:
    - Native integration of WhatsApp into the main `Fast2sms` facade.
    - New fluent interface for WhatsApp messages: `Fast2sms::viaWhatsApp()->to(...)->sendText(...)`.
    - Added support for fluent session messages: `Fast2sms::viaWhatsApp()->type(WhatsAppType::TEXT)->body(...)->send()`.
    - Unified faking and testing support for both SMS and WhatsApp.
    - Added asynchronous sending (queueing) support for both SMS and WhatsApp via `.queue()`.
- **Compatibility**:
    - Laravel 12.x support.
    - PHP 8.3+ support.

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
