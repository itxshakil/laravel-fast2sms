<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use Illuminate\Support\Collection;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Defines the contract for the WhatsApp service.
 */
interface WhatsAppInterface
{
    /**
     * Send a simplified session message.
     *
     * @param string               $to            Recipient mobile number.
     * @param array<string, mixed> $messageBody   The message body (text or media).
     * @param string|null          $phoneNumberId Optional phone number ID.
     */
    public function sendSessionMessage(string $to, array $messageBody, ?string $phoneNumberId = null): WhatsAppResponse;

    /**
     * Send a session message using META format.
     *
     * @param string               $to            Recipient mobile number.
     * @param array<string, mixed> $payload       The full META format payload.
     * @param string|null          $phoneNumberId Optional phone number ID.
     * @param string|null          $version       Optional API version.
     */
    public function sendMetaMessage(string $to, array $payload, ?string $phoneNumberId = null, ?string $version = null): WhatsAppResponse;

    /**
     * Send a simplified template message.
     *
     * @param string|array<int, string> $numbers       Recipient number(s).
     * @param string|int                $templateId    Fast2SMS template ID.
     * @param array<int, string>|null   $variables     Optional template variables.
     * @param string|null               $mediaUrl      Optional media URL.
     * @param string|null               $phoneNumberId Optional phone number ID.
     */
    public function sendTemplateMessage(string|array $numbers, string|int $templateId, ?array $variables = null, ?string $mediaUrl = null, ?string $phoneNumberId = null): WhatsAppResponse;

    /**
     * Manage templates (Create, Get, Delete).
     *
     * @param string               $method HTTP method.
     * @param string|null          $path   Additional path.
     * @param array<string, mixed> $data   Request data.
     */
    public function manageTemplates(string $method, ?string $path = null, array $data = []): WhatsAppResponse;

    /**
     * Get WABA and Template details.
     *
     * @param string $type 'number' or 'template'.
     */
    public function getWabaDetails(string $type): WhatsAppResponse;

    /**
     * Set the recipient mobile number.
     *
     * @param string|array<int, string>|Collection<int, string> $to
     */
    public function to(string|array|Collection $to): self;

    /**
     * Set the sender phone number ID.
     */
    public function from(string $phoneNumberId): self;

    /**
     * Set the message type.
     */
    public function type(WhatsAppType $type): self;

    /**
     * Set the message body text.
     */
    public function body(string $text): self;

    /**
     * Send a text message.
     */
    public function sendText(string $message): WhatsAppResponse;

    /**
     * Send an image message.
     */
    public function sendImage(string $url, ?string $caption = null): WhatsAppResponse;

    /**
     * Send a document message.
     */
    public function sendDocument(string $url, ?string $filename = null, ?string $caption = null): WhatsAppResponse;

    /**
     * Set the template ID for template messages.
     */
    public function template(string|int $templateId): self;

    /**
     * Set variables for template messages.
     *
     * @param array<int, string>|array<string, mixed> $variables
     */
    public function variables(array $variables): self;

    /**
     * Set media URL for template messages.
     */
    public function media(string $url): self;

    /**
     * Set the document filename for template messages.
     */
    public function documentFilename(string $filename): self;

    /**
     * Send the configured template message.
     */
    public function send(): WhatsAppResponse;

    /**
     * Send an interactive message.
     *
     * @param array<string, mixed> $interactive The interactive object (list, buttons, etc.)
     */
    public function sendInteractive(array $interactive): WhatsAppResponse;

    /**
     * Send a location message.
     */
    public function sendLocation(float $latitude, float $longitude, ?string $name = null, ?string $address = null): WhatsAppResponse;

    /**
     * Send a reaction message.
     */
    public function sendReaction(string $messageId, string $emoji): WhatsAppResponse;

    /**
     * Set components for template messages (Meta format).
     *
     * @param array<int, array<string, mixed>> $components
     */
    public function components(array $components): self;

    /**
     * Block one or more users.
     *
     * @param string|array<int, string> $numbers
     */
    public function block(string|array $numbers): WhatsAppResponse;

    /**
     * Unblock one or more users.
     *
     * @param string|array<int, string> $numbers
     */
    public function unblock(string|array $numbers): WhatsAppResponse;

    /**
     * Get the list of blocked users.
     */
    public function getBlockedUsers(): WhatsAppResponse;

    /**
     * Get delivery report for a specific request ID.
     */
    public function getDeliveryReport(string $requestId): WhatsAppResponse;

    /**
     * Get WhatsApp logs.
     */
    public function getLogs(string $from, string $to): WhatsAppResponse;

    /**
     * Get WhatsApp logs summary.
     */
    public function getSummary(string $from, string $to): WhatsAppResponse;

    /**
     * Get WhatsApp business profile.
     */
    public function getBusinessProfile(): WhatsAppResponse;

    /**
     * Update WhatsApp business profile.
     *
     * @param array<string, mixed> $profile
     */
    public function updateBusinessProfile(array $profile): WhatsAppResponse;

    /**
     * Get WhatsApp phone numbers.
     */
    public function getPhoneNumbers(): WhatsAppResponse;

    /**
     * Get single phone number details.
     */
    public function getPhoneNumberDetails(?string $phoneNumberId = null): WhatsAppResponse;

    /**
     * Get WABA health status.
     */
    public function getWabaHealthStatus(?string $wabaId = null): WhatsAppResponse;

    /**
     * Upload media to WhatsApp.
     *
     * @param string $filePath Path to the file.
     * @param string $type     MIME type.
     */
    public function uploadMedia(string $filePath, string $type): WhatsAppResponse;

    /**
     * Queue the WhatsApp message.
     */
    public function queue(): void;

    /**
     * Set the queue connection.
     */
    public function onConnection(string $connection): self;

    /**
     * Set the queue name.
     */
    public function onQueue(string $queue): self;

    /**
     * Set the queue delay.
     */
    public function delay(int $seconds): self;
}
