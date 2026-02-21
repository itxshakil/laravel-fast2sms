<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Illuminate\Support\Facades\Http;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Trait ManagesWhatsAppAccount.
 *
 * Handles WhatsApp account-related API calls (WABA details, profile, phone numbers, health).
 */
trait ManagesWhatsAppAccount
{
    /**
     * Get WhatsApp Business Account details.
     */
    public function getWabaDetails(string $type = 'sender'): WhatsAppResponse
    {
        try {
            $response = Http::get(config('fast2sms.base_url') . '/dlt_manager/whatsapp', [
                'authorization' => $this->apiKey,
                'type' => $type,
            ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get the business profile for a specific phone number.
     */
    public function getBusinessProfile(): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$this->defaultPhoneNumberId}/whatsapp_business_profile");

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Update the business profile for a specific phone number.
     *
     * @param array<string, mixed> $profile
     */
    public function updateBusinessProfile(array $profile): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post(config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$this->defaultPhoneNumberId}/whatsapp_business_profile", $profile);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get the phone numbers associated with the WABA.
     */
    public function getPhoneNumbers(): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$this->defaultWabaId}/phone_numbers");

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get details for a specific phone number.
     */
    public function getPhoneNumberDetails(?string $phoneNumberId = null): WhatsAppResponse
    {
        $phoneNumberId ??= $this->defaultPhoneNumberId;

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}");

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get the health status of the WABA.
     */
    public function getWabaHealthStatus(?string $wabaId = null): WhatsAppResponse
    {
        $wabaId ??= $this->defaultWabaId;

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$wabaId}");

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }
}
