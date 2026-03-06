<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Interface for WhatsApp account management operations.
 */
interface WhatsAppAccountInterface
{
    /**
     * Get WABA and Template details.
     *
     * @param string $type 'number' or 'template'.
     */
    public function getWabaDetails(string $type): WhatsAppResponse;

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
}
