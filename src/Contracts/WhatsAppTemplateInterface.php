<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Interface for WhatsApp template management operations.
 */
interface WhatsAppTemplateInterface
{
    /**
     * Send a simplified template message.
     *
     * @param string|array<int, string> $numbers          Recipient number(s).
     * @param string|int                $templateId       Fast2SMS template ID.
     * @param array<int, string>|null   $variables        Optional template variables.
     * @param string|null               $mediaUrl         Optional media URL.
     * @param string|null               $phoneNumberId    Optional phone number ID.
     * @param string|null               $documentFilename Optional document filename.
     */
    public function sendTemplateMessage(string|array $numbers, string|int $templateId, ?array $variables = null, ?string $mediaUrl = null, ?string $phoneNumberId = null, ?string $documentFilename = null): WhatsAppResponse;

    /**
     * Manage templates (Create, Get, Delete).
     *
     * @param string               $method HTTP method.
     * @param string|null          $path   Additional path.
     * @param array<string, mixed> $data   Request data.
     */
    public function manageTemplates(string $method, ?string $path = null, array $data = []): WhatsAppResponse;
}
