<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use InvalidArgumentException;

use function is_array;

use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Throwable;

trait ManagesWhatsAppTemplates
{
    /**
     * @throws Throwable
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
            'authorization' => $this->config->apiKey,
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
            $rawData = $this->client->get('/whatsapp', $query)->getRawData();

            return $this->makeWhatsAppResponse($query, $rawData);
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($query, $e));
            }
            throw $e;
        }
    }

    /**
     * Create, update, or delete WhatsApp templates.
     *
     * @param array<string, mixed> $data
     *
     * @throws Throwable
     */
    public function manageTemplates(string $method, ?string $path = null, array $data = []): WhatsAppResponse
    {
        $wabaId = $this->defaultWabaId;
        $version = $this->version;
        $url = "/whatsapp/$version/$wabaId/message_templates";

        if ($path) {
            $url .= '/' . mb_ltrim($path, '/');
        }

        $method = mb_strtolower($method);

        try {
            $response = match ($method) {
                'get' => $this->client->get($url, $data),
                'post' => $this->client->post($url, $data),
                'delete' => $this->client->delete($url, $data),
                default => throw new InvalidArgumentException("Unsupported method: {$method}"),
            };

            $rawData = $response->getRawData();

            return $this->makeWhatsAppResponse($data, $rawData);
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($data, $e));
            }
            throw $e;
        }
    }
}
