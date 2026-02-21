<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Illuminate\Support\Facades\Http;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Trait ManagesWhatsAppTemplates.
 *
 * Handles WhatsApp template-related API calls.
 */
trait ManagesWhatsAppTemplates
{
    /**
     * Send a simplified template message.
     */
    public function sendTemplateMessage(
        string|array $numbers,
        string|int $templateId,
        ?array $variables = null,
        ?string $mediaUrl = null,
        ?string $phoneNumberId = null,
        ?string $documentFilename = null,
    ): WhatsAppResponse {
        $phoneNumberId ??= $this->defaultPhoneNumberId;
        $numbersStr = is_array($numbers) ? implode(',', $numbers) : $numbers;

        $query = [
            'authorization' => $this->apiKey,
            'message_id' => $templateId,
            'phone_number_id' => $phoneNumberId,
            'numbers' => $numbersStr,
        ];

        if ($variables) {
            $query['variables_values'] = implode('|', $variables);
        }

        if ($mediaUrl) {
            $query['media_url'] = $mediaUrl;
        }

        if ($documentFilename) {
            $query['document_filename'] = $documentFilename;
        }

        try {
            $response = Http::get(config('fast2sms.base_url') . '/whatsapp', $query);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Create, update, or delete WhatsApp templates.
     *
     * @param array<string, mixed> $data
     */
    public function manageTemplates(string $method, ?string $path = null, array $data = []): WhatsAppResponse
    {
        $wabaId = $this->defaultWabaId;
        $version = $this->version;
        $url = config('fast2sms.base_url') . "/whatsapp/{$version}/{$wabaId}/message_templates";

        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])->send($method, $url, [
                'json' => $data,
            ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }
}
