<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Throwable;

trait ManagesWhatsAppAccount
{
    /**
     * Get WhatsApp Business Account details.
     *
     * @throws Throwable
     */
    public function getWabaDetails(string $type = 'sender'): WhatsAppResponse
    {
        $payload = [
            'authorization' => $this->config->apiKey,
            'type' => $type,
        ];

        try {
            return new WhatsAppResponse($this->client->get('/dlt_manager/whatsapp', $payload)->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Get the business profile for a specific phone number.
     *
     * @throws Throwable
     */
    public function getBusinessProfile(): WhatsAppResponse
    {
        $payload = [];

        try {
            return new WhatsAppResponse($this->client->get("/whatsapp/{$this->version}/{$this->defaultPhoneNumberId}/whatsapp_business_profile")->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Update the business profile for a specific phone number.
     *
     * @param array<string, mixed> $profile
     *
     * @throws Throwable
     */
    public function updateBusinessProfile(array $profile): WhatsAppResponse
    {
        try {
            return new WhatsAppResponse($this->client->post("/whatsapp/{$this->version}/{$this->defaultPhoneNumberId}/whatsapp_business_profile", $profile)->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($profile, $e));
            }
            throw $e;
        }
    }

    /**
     * Get the phone numbers associated with the WABA.
     *
     * @throws Throwable
     */
    public function getPhoneNumbers(): WhatsAppResponse
    {
        $payload = [];

        try {
            return new WhatsAppResponse($this->client->get("/whatsapp/{$this->version}/{$this->defaultWabaId}/phone_numbers")->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Get details for a specific phone number.
     *
     * @throws Throwable
     */
    public function getPhoneNumberDetails(?string $phoneNumberId = null): WhatsAppResponse
    {
        $phoneNumberId ??= $this->defaultPhoneNumberId;
        $payload = ['phone_number_id' => $phoneNumberId];

        try {
            return new WhatsAppResponse($this->client->get("/whatsapp/{$this->version}/{$phoneNumberId}")->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Get the health status of the WABA.
     *
     * @throws Throwable
     */
    public function getWabaHealthStatus(?string $wabaId = null): WhatsAppResponse
    {
        $wabaId ??= $this->defaultWabaId;
        $payload = ['waba_id' => $wabaId];

        try {
            return new WhatsAppResponse($this->client->get("/whatsapp/{$this->version}/{$wabaId}")->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }
}
