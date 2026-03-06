<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Override;
use Psr\SimpleCache\InvalidArgumentException;
use Shakil\Fast2sms\Contracts\ClientInterface;
use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\DataTransferObjects\Fast2smsConfig;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Events\WhatsAppSent;
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Shakil\Fast2sms\Traits\AppliesSendGuards;
use Shakil\Fast2sms\Traits\ManagesWhatsAppAccount;
use Shakil\Fast2sms\Traits\ManagesWhatsAppActions;
use Shakil\Fast2sms\Traits\ManagesWhatsAppParameters;
use Shakil\Fast2sms\Traits\ManagesWhatsAppTemplates;
use Throwable;

/**
 * Service class for interacting with the Fast2sms WhatsApp API.
 */
class WhatsApp extends BaseFast2smsService implements WhatsAppInterface
{
    use AppliesSendGuards;
    use ManagesWhatsAppAccount;
    use ManagesWhatsAppActions;
    use ManagesWhatsAppParameters;
    use ManagesWhatsAppTemplates;

    protected string $defaultPhoneNumberId;

    protected string $defaultWabaId;

    protected string $version;

    /**
     * Create a new WhatsApp instance.
     *
     * @param ClientInterface $client The API client.
     * @param Fast2smsConfig  $config The typed package configuration.
     */
    public function __construct(ClientInterface $client, Fast2smsConfig $config)
    {
        parent::__construct($client, $config);

        $this->defaultPhoneNumberId = $config->whatsappPhoneNumberId;
        $this->defaultWabaId = $config->whatsappWabaId;
        $this->version = $config->whatsappVersion;
    }

    /**
     * Send a simplified session message.
     *
     * @param array<array<string, mixed>, mixed> $messageBody
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws ThrottleExceededException
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function sendSessionMessage(string $to, array $messageBody, ?string $phoneNumberId = null): WhatsAppResponse
    {
        $commitDedup = $this->applySendGuards($to . '|' . json_encode($messageBody));

        $phoneNumberId ??= $this->defaultPhoneNumberId;
        $payload = array_merge(['to' => $to, 'phone_number_id' => $phoneNumberId], $messageBody);

        try {
            $response = $this->client->post('/whatsapp-session', $payload);
            $result = $this->makeWhatsAppResponse($payload, $response->getRawData());
            $commitDedup();

            return $result;
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Send a session message using META format.
     *
     * @param array<array<string, mixed>, mixed> $payload
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     * @throws ThrottleExceededException
     * @throws Throwable
     */
    public function sendMetaMessage(string $to, array $payload, ?string $phoneNumberId = null, ?string $version = null): WhatsAppResponse
    {
        $commitDedup = $this->applySendGuards($to . '|' . json_encode($payload));

        $phoneNumberId ??= $this->defaultPhoneNumberId;
        $version ??= $this->version;
        $fullPayload = array_merge(['messaging_product' => 'whatsapp', 'to' => $to], $payload);

        try {
            $response = $this->client->post("/whatsapp/{$version}/{$phoneNumberId}/messages", $fullPayload);
            $result = $this->makeWhatsAppResponse($fullPayload, $response->getRawData());
            $commitDedup();

            return $result;
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($fullPayload, $e));
            }
            throw $e;
        }
    }

    /**
     * Send a text message.
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     * @throws ThrottleExceededException
     * @throws Throwable
     */
    public function sendText(string $message): WhatsAppResponse
    {
        try {
            return $this->sendSessionMessage($this->getTo() ?? '', [
                'type' => 'text',
                'text' => ['body' => $message],
            ], $this->getFromPhoneNumberId());
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Send an image message.
     *
     * @throws InvalidArgumentException
     * @throws ThrottleExceededException
     * @throws InsufficientBalanceException
     * @throws DuplicateSendException
     * @throws Throwable
     */
    public function sendImage(string $url, ?string $caption = null): WhatsAppResponse
    {
        try {
            $image = ['link' => $url];
            if ($caption) {
                $image['caption'] = $caption;
            }

            return $this->sendSessionMessage($this->getTo() ?? '', [
                'type' => 'image',
                'image' => $image,
            ], $this->getFromPhoneNumberId());
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Send a document message.
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     * @throws ThrottleExceededException
     * @throws Throwable
     */
    public function sendDocument(string $url, ?string $filename = null, ?string $caption = null): WhatsAppResponse
    {
        try {
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
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Send the WhatsApp message using fluent parameters.
     *
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     * @throws ThrottleExceededException
     * @throws Throwable
     */
    public function send(): WhatsAppResponse
    {
        try {
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
                } elseif ($this->getType() === WhatsAppType::REACTION) {
                    return $this->sendReaction($this->getMessageId() ?? '', $this->getEmoji() ?? '');
                } elseif ($this->getType() === WhatsAppType::LOCATION) {
                    $loc = $this->getLocation() ?? [];

                    return $this->sendLocation(
                        (float) ($loc['latitude'] ?? 0),
                        (float) ($loc['longitude'] ?? 0),
                        $loc['name'] ?? null,
                        $loc['address'] ?? null,
                    );
                } elseif ($this->getType() === WhatsAppType::INTERACTIVE) {
                    return $this->sendInteractive($this->getInteractive() ?? []);
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
                } else {
                    throw new ValidationException('Unsupported WhatsApp message type: ' . $this->getType()->value);
                }

                return $this->sendSessionMessage($this->getTo() ?? '', $payload, $this->getFromPhoneNumberId());
            }

            throw new ValidationException('Template ID or Message Type is required for sending WhatsApp messages.');
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
     * Create a WhatsAppResponse and dispatch the WhatsAppSent event.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $data
     */
    protected function makeWhatsAppResponse(array $payload, array $data): WhatsAppResponse
    {
        $response = new WhatsAppResponse($data);

        if (config('fast2sms.events.enabled', true)) {
            event(new WhatsAppSent($payload, $response));
        }

        return $response;
    }
}
