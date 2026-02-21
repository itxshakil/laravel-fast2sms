<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Override;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Enums\WhatsAppType;

use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Traits\ManagesWhatsAppAccount;
use Shakil\Fast2sms\Traits\ManagesWhatsAppParameters;
use Shakil\Fast2sms\Traits\ManagesWhatsAppTemplates;

/**
 * Service class for interacting with the Fast2sms WhatsApp API.
 */
class WhatsApp extends BaseFast2smsService implements WhatsAppInterface
{
    use ManagesWhatsAppAccount;
    use ManagesWhatsAppParameters;
    use ManagesWhatsAppTemplates;

    protected string $defaultPhoneNumberId;

    protected string $defaultWabaId;

    protected string $version;

    /**
     * Create a new WhatsApp instance.
     *
     * @throws Fast2smsException
     */
    public function __construct()
    {
        parent::__construct();

        $this->defaultPhoneNumberId = (string) config('fast2sms.whatsapp.default_phone_number_id');
        $this->defaultWabaId = (string) config('fast2sms.whatsapp.default_waba_id');
        $this->version = (string) config('fast2sms.whatsapp.version', 'v24.0');
    }

    /**
     * Send a simplified session message.
     *
     * @param array<array<string, mixed>, mixed> $messageBody
     */
    public function sendSessionMessage(string $to, array $messageBody, ?string $phoneNumberId = null): WhatsAppResponse
    {
        $phoneNumberId ??= $this->defaultPhoneNumberId;

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post(config('fast2sms.base_url') . '/whatsapp-session', array_merge([
                'to' => $to,
                'phone_number_id' => $phoneNumberId,
            ], $messageBody));

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Send a session message using META format.
     *
     * @param array<array<string, mixed>, mixed> $payload
     */
    public function sendMetaMessage(string $to, array $payload, ?string $phoneNumberId = null, ?string $version = null): WhatsAppResponse
    {
        $phoneNumberId ??= $this->defaultPhoneNumberId;
        $version ??= $this->version;

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post(config('fast2sms.base_url') . "/whatsapp/{$version}/{$phoneNumberId}/messages", array_merge([
                'messaging_product' => 'whatsapp',
                'to' => $to,
            ], $payload));

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }


    /**
     * Send a text message.
     */
    public function sendText(string $message): WhatsAppResponse
    {
        return $this->sendSessionMessage($this->getTo() ?? '', [
            'type' => 'text',
            'text' => ['body' => $message],
        ], $this->getFromPhoneNumberId());
    }

    /**
     * Send an image message.
     */
    public function sendImage(string $url, ?string $caption = null): WhatsAppResponse
    {
        $image = ['link' => $url];
        if ($caption) {
            $image['caption'] = $caption;
        }

        return $this->sendSessionMessage($this->getTo() ?? '', [
            'type' => 'image',
            'image' => $image,
        ], $this->getFromPhoneNumberId());
    }

    /**
     * Send a document message.
     */
    public function sendDocument(string $url, ?string $filename = null, ?string $caption = null): WhatsAppResponse
    {
        $document = ['link' => $url];
        if ($filename) {
            $document['filename'] = $filename;
        }
        if ($caption) {
            $document['caption'] = $caption;
        }

        return $this->sendSessionMessage($this->getTo() ?? '', [
            'type' => 'document',
            'document' => $document,
        ], $this->getFromPhoneNumberId());
    }

    /**
     * Send the WhatsApp message using fluent parameters.
     */
    public function send(): WhatsAppResponse
    {
        if ($this->getTemplateId()) {
            if ($this->getComponents()) {
                return $this->sendMetaMessage($this->getTo() ?? '', [
                    'type' => 'template',
                    'template' => [
                        'name' => $this->getTemplateId(),
                        'language' => ['code' => config('fast2sms.whatsapp.language', 'en_US')],
                        'components' => $this->getComponents(),
                    ],
                ], $this->getFromPhoneNumberId());
            }

            return $this->sendTemplateMessage(
                $this->getTo() ?? '',
                $this->getTemplateId(),
                $this->getVariables(),
                $this->getMediaUrl(),
                $this->getFromPhoneNumberId(),
                $this->getDocumentFilename(),
            );
        }

        if ($this->getType() instanceof WhatsAppType) {
            $payload = [
                'type' => $this->getType()->value,
            ];

            if ($this->getType() === WhatsAppType::TEXT) {
                $payload['text'] = ['body' => $this->getBody()];
            } elseif (in_array($this->getType(), [WhatsAppType::IMAGE, WhatsAppType::VIDEO, WhatsAppType::AUDIO, WhatsAppType::DOCUMENT, WhatsAppType::STICKER])) {
                $payload[$this->getType()->value] = [
                    'link' => $this->getMediaUrl(),
                ];
                if ($this->getBody()) {
                    $payload[$this->getType()->value]['caption'] = $this->getBody();
                }
                if ($this->getType() === WhatsAppType::DOCUMENT && $this->getDocumentFilename()) {
                    $payload['document']['filename'] = $this->getDocumentFilename();
                }
            }

            return $this->sendSessionMessage($this->getTo() ?? '', $payload, $this->getFromPhoneNumberId());
        }

        throw new Fast2smsException('Template ID or Message Type is required for sending WhatsApp messages.');
    }

    /**
     * Send an interactive message.
     */
    public function sendInteractive(array $interactive): WhatsAppResponse
    {
        return $this->sendMetaMessage($this->getTo() ?? '', [
            'type' => 'interactive',
            'interactive' => $interactive,
        ]);
    }

    /**
     * Send a location message.
     */
    public function sendLocation(float $latitude, float $longitude, ?string $name = null, ?string $address = null): WhatsAppResponse
    {
        $location = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($name) {
            $location['name'] = $name;
        }

        if ($address) {
            $location['address'] = $address;
        }

        return $this->sendMetaMessage($this->getTo() ?? '', [
            'type' => 'location',
            'location' => $location,
        ]);
    }

    /**
     * Send a reaction message.
     */
    public function sendReaction(string $messageId, string $emoji): WhatsAppResponse
    {
        return $this->sendMetaMessage($this->getTo() ?? '', [
            'type' => 'reaction',
            'reaction' => [
                'message_id' => $messageId,
                'emoji' => $emoji,
            ],
        ]);
    }

    /**
     * Block one or more users.
     *
     * @param string|array<int, string> $numbers
     */
    public function block(string|array $numbers): WhatsAppResponse
    {
        $numbers = is_array($numbers) ? $numbers : [$numbers];
        $blockUsers = array_map(fn (string $n): array => ['input' => $n], $numbers);

        $phoneNumberId = $this->getFromPhoneNumberId() ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/block_users";

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post($url, [
                'messaging_product' => 'whatsapp',
                'block_users' => $blockUsers,
            ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Unblock one or more users.
     *
     * @param string|array<int, string> $numbers
     */
    public function unblock(string|array $numbers): WhatsAppResponse
    {
        $numbers = is_array($numbers) ? $numbers : [$numbers];
        $blockUsers = array_map(fn (string $n): array => ['input' => $n], $numbers);

        $phoneNumberId = $this->getFromPhoneNumberId() ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/block_users";

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])->delete($url, [
                'messaging_product' => 'whatsapp',
                'block_users' => $blockUsers,
            ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get the list of blocked users.
     */
    public function getBlockedUsers(): WhatsAppResponse
    {
        $phoneNumberId = $this->getFromPhoneNumberId() ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/block_users";

        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get($url);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get delivery report for a specific request ID.
     */
    public function getDeliveryReport(string $requestId): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . "/whatsapp/{$requestId}");

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get WhatsApp logs.
     */
    public function getLogs(string $from, string $to): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . '/whatsapp_logs', [
                'from' => $from,
                'to' => $to,
            ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get WhatsApp logs summary.
     */
    public function getSummary(string $from, string $to): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->get(config('fast2sms.base_url') . '/whatsapp_summary', [
                'from' => $from,
                'to' => $to,
            ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }


    /**
     * Upload media to WhatsApp.
     */
    public function uploadMedia(string $filePath, string $type): WhatsAppResponse
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->attach('file', file_get_contents($filePath), basename($filePath))
                ->post(config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$this->defaultPhoneNumberId}/media", [
                    'type' => $type,
                    'messaging_product' => 'whatsapp',
                ]);

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Hook method executed after every API call.
     *
     * Reset the fluent state for the next request.
     */
    #[Override]
    protected function afterApiCall(): void
    {
        $this->resetWhatsAppParameters();
    }

    /**
     * Handle the WhatsApp API response.
     */
    protected function handleWhatsAppResponse(Response $response): WhatsAppResponse
    {
        if (self::$faking) {
            return new WhatsAppResponse([
                'return' => true,
                'success' => true,
                'status' => true,
                'message' => 'Message sent successfully (faked).',
            ]);
        }

        return new WhatsAppResponse($response->json() ?? []);
    }
}
