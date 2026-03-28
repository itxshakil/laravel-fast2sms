<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Responses;

use function is_array;

class DltManagerResponse extends Fast2smsResponse
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSenders(): array
    {
        return array_map(static fn (array $item): array => [
            'sender_id' => $item['sender_id'] ?? null,
            'entity_id' => $item['entity_id'] ?? null,
            'entity_name' => $item['entity_name'] ?? null,
        ], $this->getData());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->data['data'] ?? [];
    }

    /**
     * @return array<int, mixed>
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
