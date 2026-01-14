<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

/**
 * A class to handle DLT (Distributed Ledger Technology) specific responses from the Fast2sms API.
 *
 * This class extends the base Fast2smsResponse and provides specialized methods
 * to access DLT-related data, such as sender information and templates.
 */
class DltManagerResponse extends Fast2smsResponse
{
    /**
     * Gets a formatted array of sender information.
     *
     * Each sender's data is normalized to include 'sender_id', 'entity_id', and 'entity_name'.
     *
     * @return mixed[][] An array of sender data, with each element being an associative array.
     */
    public function getSenders(): array
    {
        return array_map(fn (array $item): array => [
            'sender_id' => $item['sender_id'] ?? null,
            'entity_id' => $item['entity_id'] ?? null,
            'entity_name' => $item['entity_name'] ?? null,
        ], $this->getData());
    }

    /**
     * Gets the 'data' array from the response.
     *
     * This method is useful for accessing the core DLT information within the response.
     *
     * @return array<int, array<string, mixed>> The 'data' array from the response, or an empty array if not present.
     */
    public function getData(): array
    {
        return $this->data['data'] ?? [];
    }

    /**
     * Gets an array of all templates from the response data.
     *
     * This method iterates through the response data and aggregates all 'templates'
     * from each item into a single, flat array.
     *
     * @return array<int, mixed> A consolidated array of all template data found in the response.
     */
    public function getTemplates(): array
    {
        $templates = [];
        foreach ($this->getData() as $item) {
            if (isset($item['templates']) && is_array($item['templates'])) {
                $templates = array_merge($templates, $item['templates']);
            }
        }

        return $templates;
    }
}
