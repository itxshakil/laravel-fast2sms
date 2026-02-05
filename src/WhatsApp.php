<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Override;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;

use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Jobs\SendWhatsAppJob;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * Service class for interacting with the Fast2sms WhatsApp API.
 */
class WhatsApp extends BaseFast2smsService implements WhatsAppInterface
{
    protected string $defaultPhoneNumberId;

    protected string $defaultWabaId;

    protected string $version;

    protected ?string $to = null;

    protected ?string $fromPhoneNumberId = null;

    protected ?WhatsAppType $type = null;

    protected ?string $body = null;

    protected ?string $templateId = null;

    /**
     * @var array<int, string>|array<string, mixed>|null
     */
    protected ?array $variables = null;

    protected ?string $mediaUrl = null;

    protected ?string $documentFilename = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $components = null;

    protected ?string $queueConnection = null;

    protected ?string $queueName = null;

    protected ?int $queueDelay = null;

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
                'to' => $to,
            ], $payload));

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

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

        if ($variables !== null) {
            $query['variables_values'] = implode('|', $variables);
        }

        if ($mediaUrl !== null) {
            $query['media_url'] = $mediaUrl;
        }

        if ($documentFilename !== null) {
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
     * Manage templates (Create, Get, Delete).
     */
    public function manageTemplates(string $method, ?string $path = null, array $data = []): WhatsAppResponse
    {
        $wabaId = $this->defaultWabaId;
        $version = $this->version;
        $url = config('fast2sms.base_url') . "/whatsapp/{$version}/{$wabaId}/message_templates";

        if ($path !== null) {
            $url .= '/' . mb_ltrim($path, '/');
        }

        $request = Http::withHeaders([
            'authorization' => $this->apiKey,
            'content-type' => 'application/json',
        ]);

        try {
            $response = match (mb_strtoupper($method)) {
                'POST' => $request->post($url, $data),
                'GET' => $request->get($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new Fast2smsException("Unsupported method: {$method}"),
            };

            return $this->handleWhatsAppResponse($response);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get WABA and Template details.
     */
    public function getWabaDetails(string $type): WhatsAppResponse
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
     * Set the recipient mobile number.
     *
     * @param string|array<int, string>|Collection<int, string> $to
     */
    public function to(string|array|Collection $to): self
    {
        if ($to instanceof Collection) {
            $to = $to->toArray();
        }

        $this->to = is_array($to) ? implode(',', $to) : (string) $to;

        return $this;
    }

    /**
     * Set the sender phone number ID.
     */
    public function from(string $phoneNumberId): self
    {
        $this->fromPhoneNumberId = $phoneNumberId;

        return $this;
    }

    /**
     * Set the message type.
     */
    public function type(WhatsAppType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the message body text.
     */
    public function body(string $text): self
    {
        $this->body = $text;

        return $this;
    }

    /**
     * Send a text message.
     */
    public function sendText(string $message): WhatsAppResponse
    {
        return $this->sendSessionMessage($this->to, [
            'type' => 'text',
            'text' => ['body' => $message],
        ], $this->fromPhoneNumberId);
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

        return $this->sendSessionMessage($this->to, [
            'type' => 'image',
            'image' => $image,
        ], $this->fromPhoneNumberId);
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

        return $this->sendSessionMessage($this->to, [
            'type' => 'document',
            'document' => $document,
        ], $this->fromPhoneNumberId);
    }

    /**
     * Set the template ID for template messages.
     */
    public function template(string|int $templateId): self
    {
        $this->templateId = (string) $templateId;

        return $this;
    }

    /**
     * Set variables for template messages.
     *
     * @param array<int, string>|array<string, mixed> $variables
     */
    public function variables(array $variables): self
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Set media URL for template messages.
     */
    public function media(string $url): self
    {
        $this->mediaUrl = $url;

        return $this;
    }

    /**
     * Set the document filename for template messages.
     */
    public function documentFilename(string $filename): self
    {
        $this->documentFilename = $filename;

        return $this;
    }

    /**
     * Set components for template messages (Meta format).
     *
     * @param array<int, array<string, mixed>> $components
     */
    public function components(array $components): self
    {
        $this->components = $components;

        return $this;
    }

    /**
     * Send the configured template message.
     */
    public function send(): WhatsAppResponse
    {
        if ($this->templateId) {
            if ($this->components) {
                return $this->sendMetaMessage($this->to, [
                    'messaging_product' => 'whatsapp',
                    'type' => 'template',
                    'template' => [
                        'name' => $this->templateId,
                        'language' => ['code' => config('fast2sms.whatsapp.language', 'en_US')],
                        'components' => $this->components,
                    ],
                ], $this->fromPhoneNumberId);
            }

            return $this->sendTemplateMessage(
                $this->to,
                $this->templateId,
                $this->variables,
                $this->mediaUrl,
                $this->fromPhoneNumberId,
                $this->documentFilename,
            );
        }

        if ($this->type instanceof WhatsAppType) {
            $payload = [
                'type' => $this->type->value,
            ];

            if ($this->type === WhatsAppType::TEXT) {
                $payload['text'] = ['body' => $this->body];
            } elseif (in_array($this->type, [WhatsAppType::IMAGE, WhatsAppType::VIDEO, WhatsAppType::AUDIO, WhatsAppType::DOCUMENT, WhatsAppType::STICKER])) {
                $payload[$this->type->value] = [
                    'link' => $this->mediaUrl,
                ];
                if ($this->body) {
                    $payload[$this->type->value]['caption'] = $this->body;
                }
                if ($this->type === WhatsAppType::DOCUMENT && $this->documentFilename) {
                    $payload['document']['filename'] = $this->documentFilename;
                }
            }

            return $this->sendSessionMessage($this->to, $payload, $this->fromPhoneNumberId);
        }

        throw new Fast2smsException('Template ID or Message Type is required for sending WhatsApp messages.');
    }

    /**
     * Send an interactive message.
     */
    public function sendInteractive(array $interactive): WhatsAppResponse
    {
        return $this->sendMetaMessage($this->to, [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => $interactive,
        ], $this->fromPhoneNumberId);
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

        return $this->sendMetaMessage($this->to, [
            'messaging_product' => 'whatsapp',
            'type' => 'location',
            'location' => $location,
        ], $this->fromPhoneNumberId);
    }

    /**
     * Send a reaction message.
     */
    public function sendReaction(string $messageId, string $emoji): WhatsAppResponse
    {
        return $this->sendMetaMessage($this->to, [
            'messaging_product' => 'whatsapp',
            'type' => 'reaction',
            'reaction' => [
                'message_id' => $messageId,
                'emoji' => $emoji,
            ],
        ], $this->fromPhoneNumberId);
    }

    /**
     * Block one or more users.
     */
    public function block(string|array $numbers): WhatsAppResponse
    {
        $numbers = is_array($numbers) ? $numbers : [$numbers];
        $blockUsers = array_map(fn (string $n): array => ['input' => $n], $numbers);

        $phoneNumberId = $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/block_users";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
            'content-type' => 'application/json',
        ])->post($url, [
            'messaging_product' => 'whatsapp',
            'block_users' => $blockUsers,
        ]);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Unblock one or more users.
     */
    public function unblock(string|array $numbers): WhatsAppResponse
    {
        $numbers = is_array($numbers) ? $numbers : [$numbers];
        $blockUsers = array_map(fn (string $n): array => ['input' => $n], $numbers);

        $phoneNumberId = $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/block_users";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
            'content-type' => 'application/json',
        ])->delete($url, [
            'messaging_product' => 'whatsapp',
            'block_users' => $blockUsers,
        ]);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get the list of blocked users.
     */
    public function getBlockedUsers(): WhatsAppResponse
    {
        $phoneNumberId = $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/block_users";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get($url);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get delivery report for a specific request ID.
     */
    public function getDeliveryReport(string $requestId): WhatsAppResponse
    {
        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get(config('fast2sms.base_url') . "/whatsapp/{$requestId}");

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get WhatsApp logs.
     */
    public function getLogs(string $from, string $to): WhatsAppResponse
    {
        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get(config('fast2sms.base_url') . '/whatsapp_logs', [
            'from' => $from,
            'to' => $to,
        ]);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get WhatsApp logs summary.
     */
    public function getSummary(string $from, string $to): WhatsAppResponse
    {
        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get(config('fast2sms.base_url') . '/whatsapp_summary', [
            'from' => $from,
            'to' => $to,
        ]);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get WhatsApp business profile.
     */
    public function getBusinessProfile(): WhatsAppResponse
    {
        $phoneNumberId = $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/whatsapp_business_profile";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get($url);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Update WhatsApp business profile.
     */
    public function updateBusinessProfile(array $profile): WhatsAppResponse
    {
        $phoneNumberId = $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/whatsapp_business_profile";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
            'content-type' => 'application/json',
        ])->post($url, $profile);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get WhatsApp phone numbers.
     */
    public function getPhoneNumbers(): WhatsAppResponse
    {
        $wabaId = $this->defaultWabaId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$wabaId}/phone_numbers";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get($url);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get single phone number details.
     */
    public function getPhoneNumberDetails(?string $phoneNumberId = null): WhatsAppResponse
    {
        $phoneNumberId ??= $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get($url);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Get WABA health status.
     */
    public function getWabaHealthStatus(?string $wabaId = null): WhatsAppResponse
    {
        $wabaId ??= $this->defaultWabaId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$wabaId}";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get($url);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Upload media to WhatsApp.
     */
    public function uploadMedia(string $filePath, string $type): WhatsAppResponse
    {
        $phoneNumberId = $this->fromPhoneNumberId ?: $this->defaultPhoneNumberId;
        $url = config('fast2sms.base_url') . "/whatsapp/{$this->version}/{$phoneNumberId}/media";

        $response = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->attach('file', file_get_contents($filePath), basename($filePath))
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'type' => $type,
            ]);

        return $this->handleWhatsAppResponse($response);
    }

    /**
     * Queue the WhatsApp message.
     */
    public function queue(): void
    {
        $parameters = new WhatsAppParameters(
            to: $this->to,
            phoneNumberId: $this->fromPhoneNumberId,
            type: $this->type?->value,
            body: $this->body,
            templateId: $this->templateId,
            variables: $this->variables,
            mediaUrl: $this->mediaUrl,
            documentFilename: $this->documentFilename,
            components: $this->components,
        );

        $job = new SendWhatsAppJob($parameters);

        if ($this->queueConnection) {
            $job->onConnection($this->queueConnection);
        }

        if ($this->queueName) {
            $job->onQueue($this->queueName);
        }

        if ($this->queueDelay) {
            $job->delay($this->queueDelay);
        }

        dispatch($job);

        $this->afterApiCall();
    }

    /**
     * Set the queue connection.
     */
    public function onConnection(string $connection): self
    {
        $this->queueConnection = $connection;

        return $this;
    }

    /**
     * Set the queue name.
     */
    public function onQueue(string $queue): self
    {
        $this->queueName = $queue;

        return $this;
    }

    /**
     * Set the queue delay.
     */
    public function delay(int $seconds): self
    {
        $this->queueDelay = $seconds;

        return $this;
    }

    /**
     * Hook method executed after every API call.
     *
     * Reset the fluent state for the next request.
     */
    #[Override]
    protected function afterApiCall(): void
    {
        $this->to = null;
        $this->fromPhoneNumberId = null;
        $this->type = null;
        $this->body = null;
        $this->templateId = null;
        $this->variables = null;
        $this->mediaUrl = null;
        $this->documentFilename = null;
        $this->components = null;

        $this->queueConnection = null;
        $this->queueName = null;
        $this->queueDelay = null;
    }

    /**
     * Handle the WhatsApp API response.
     */
    protected function handleWhatsAppResponse(Response $response): WhatsAppResponse
    {
        if ($response->successful()) {
            return new WhatsAppResponse($response->json());
        }

        $error = $response->json('message') ?? $response->json('error.message') ?? 'Unknown WhatsApp API error.';
        throw new Fast2smsException("WhatsApp API Error: $error", $response->status());
    }
}
