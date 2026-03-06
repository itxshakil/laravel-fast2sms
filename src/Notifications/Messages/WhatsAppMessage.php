<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Notifications\Messages;

use function is_int;

use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Stringable;

/**
 * WhatsApp Message builder for Fast2SMS notifications.
 *
 * @property-read string|null $content The message body content
 * @property-read string|null $to The recipient number
 * @property-read WhatsAppType|null $type The message type
 * @property-read string|int|null $templateId The template ID
 * @property-read array<int, string>|array<string, mixed>|null $variables Template variables
 * @property-read string|null $mediaUrl The media URL
 * @property-read string|null $documentFilename The document filename
 * @property-read array<int, array<string, mixed>>|null $components Meta format components
 * @property-read array<string, mixed>|null $interactive Interactive message payload
 * @property-read array<string, mixed>|null $location Location payload
 * @property-read string|null $messageId The message ID to react to
 * @property-read string|null $emoji The emoji for reaction messages
 * @property-read string|null $fromPhoneNumberId The sender's WhatsApp phone number ID
 */
class WhatsAppMessage implements Stringable
{
    /**
     * The message content.
     */
    protected ?string $content = null;

    /**
     * The recipient number.
     */
    protected ?string $to = null;

    /**
     * The message type.
     */
    protected ?WhatsAppType $type = null;

    /**
     * The template ID.
     */
    protected string|int|null $templateId = null;

    /**
     * The template variables.
     *
     * @var array<int, string>|array<string, mixed>|null
     */
    protected ?array $variables = null;

    /**
     * The media URL.
     */
    protected ?string $mediaUrl = null;

    /**
     * The document filename.
     */
    protected ?string $documentFilename = null;

    /**
     * Meta format components.
     *
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $components = null;

    /**
     * Interactive message payload.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $interactive = null;

    /**
     * Location payload.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $location = null;

    /**
     * The message ID to react to.
     */
    protected ?string $messageId = null;

    /**
     * The emoji for reaction messages.
     */
    protected ?string $emoji = null;

    /**
     * The sender's WhatsApp phone number ID.
     */
    protected ?string $fromPhoneNumberId = null;

    public function __construct(string $content = '')
    {
        if ($content !== '') {
            $this->content($content);
        }
    }

    /**
     * Get a property value.
     */
    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
    }

    /**
     * Return a human-readable summary for logging.
     */
    public function __toString(): string
    {
        $type = $this->type instanceof WhatsAppType ? $this->type->value : 'unknown';
        $to = $this->to ?? '';

        return "WhatsAppMessage(to: {$to}, type: {$type})";
    }

    /**
     * Create a plain text WhatsApp message.
     */
    public static function text(string $content): self
    {
        return (new self($content))->type(WhatsAppType::TEXT);
    }

    /**
     * Create an image WhatsApp message.
     */
    public static function image(string $url, string $caption = ''): self
    {
        $instance = new self($caption);

        return $instance->type(WhatsAppType::IMAGE)->media($url);
    }

    /**
     * Create a document WhatsApp message.
     */
    public static function document(string $url, string $filename = ''): self
    {
        $instance = new self();

        return $instance->type(WhatsAppType::DOCUMENT)->media($url)->documentFilename($filename);
    }

    /**
     * Create a location WhatsApp message.
     *
     * @throws ValidationException
     */
    public static function forLocation(float $lat, float $lng, ?string $name = null, ?string $address = null): self
    {
        if ($lat < -90.0 || $lat > 90.0) {
            throw ValidationException::invalidLatitude($lat);
        }

        if ($lng < -180.0 || $lng > 180.0) {
            throw ValidationException::invalidLongitude($lng);
        }

        $payload = ['latitude' => $lat, 'longitude' => $lng];
        if ($name !== null) {
            $payload['name'] = $name;
        }

        if ($address !== null) {
            $payload['address'] = $address;
        }

        $instance = new self();

        return $instance->type(WhatsAppType::LOCATION)->location($payload);
    }

    /**
     * Create a video WhatsApp message.
     */
    public static function forVideo(string $url, string $caption = ''): self
    {
        return (new self($caption))->type(WhatsAppType::VIDEO)->media($url);
    }

    /**
     * Create an audio WhatsApp message.
     */
    public static function forAudio(string $url): self
    {
        return (new self())->type(WhatsAppType::AUDIO)->media($url);
    }

    /**
     * Create a sticker WhatsApp message.
     */
    public static function forSticker(string $url): self
    {
        return (new self())->type(WhatsAppType::STICKER)->media($url);
    }

    /**
     * Create a reaction WhatsApp message.
     */
    public static function forReaction(string $messageId, string $emoji): self
    {
        return (new self())->type(WhatsAppType::REACTION)->messageId($messageId)->emoji($emoji);
    }

    /**
     * Create an interactive WhatsApp message.
     *
     * @param array<string, mixed> $payload
     */
    public static function forInteractive(array $payload): self
    {
        $instance = new self();

        return $instance->type(WhatsAppType::INTERACTIVE)->interactive($payload);
    }

    /**
     * Set the message content (body).
     */
    public function content(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the recipient number.
     */
    public function to(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Set the WhatsApp message type.
     */
    public function type(WhatsAppType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the template and its variables.
     *
     * @param array<int, string>|array<string, mixed> $variables
     */
    public function template(string|int $templateId, array $variables = []): static
    {
        $this->templateId = $templateId;
        $this->variables = $variables;

        return $this;
    }

    /**
     * Set the media URL.
     */
    public function media(string $url): static
    {
        $this->mediaUrl = $url;

        return $this;
    }

    /**
     * Set the document filename.
     */
    public function documentFilename(string $filename): static
    {
        $this->documentFilename = $filename;

        return $this;
    }

    /**
     * Set Meta format components.
     *
     * @param array<int, array<string, mixed>> $components
     */
    public function components(array $components): static
    {
        $this->components = $components;

        return $this;
    }

    /**
     * Set interactive message payload.
     *
     * @param array<string, mixed> $interactive
     */
    public function interactive(array $interactive): static
    {
        $this->interactive = $interactive;

        return $this;
    }

    /**
     * Set location payload.
     *
     * @param array<string, mixed> $location
     */
    public function location(array $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set the message ID to react to.
     */
    public function messageId(string $messageId): static
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Set the sender's WhatsApp phone number ID.
     */
    public function from(string $phoneNumberId): static
    {
        $this->fromPhoneNumberId = $phoneNumberId;

        return $this;
    }

    /**
     * Set the emoji for reaction messages.
     */
    public function emoji(string $emoji): static
    {
        $this->emoji = $emoji;

        return $this;
    }

    /**
     * Validate the message before sending.
     *
     * @throws ValidationException
     */
    public function validate(): void
    {
        if ($this->to === null) {
            throw ValidationException::missingRecipient();
        }

        if (! $this->type instanceof WhatsAppType && $this->content === null && $this->interactive === null && $this->location === null) { // @phpstan-ignore-line
            throw ValidationException::missingWhatsAppContent();
        }
    }

    /**
     * Build a WhatsAppParameters DTO from this message.
     *
     * @throws ValidationException
     */
    public function toWhatsAppParameters(): WhatsAppParameters
    {
        $this->validate();

        return new WhatsAppParameters(
            to: $this->to ?? '',
            phoneNumberId: $this->fromPhoneNumberId,
            type: $this->type,
            body: $this->content,
            templateId: is_int($this->templateId) ? (string) $this->templateId : $this->templateId,
            variables: $this->variables,
            mediaUrl: $this->mediaUrl,
            documentFilename: $this->documentFilename,
            messageId: $this->messageId,
            emoji: $this->emoji,
            components: $this->components,
            location: $this->location,
            interactive: $this->interactive,
        );
    }

    /**
     * Send the message immediately.
     *
     * @throws Fast2smsException
     */
    public function send(): WhatsAppResponse
    {
        if (! $this->to) {
            throw new Fast2smsException('Recipient number is required for WhatsApp message.');
        }

        $service = Fast2sms::viaWhatsApp($this->to);

        if ($this->fromPhoneNumberId) {
            $service->from($this->fromPhoneNumberId);
        }

        $type = $this->type ?? WhatsAppType::TEXT;
        $service->type($type);

        if ($this->templateId) {
            $service->template($this->templateId);
        }

        if ($this->variables) {
            $service->variables($this->variables);
        }

        if ($this->mediaUrl) {
            $service->media($this->mediaUrl);
        }

        if ($this->documentFilename) {
            $service->documentFilename($this->documentFilename);
        }

        if ($this->components) {
            $service->components($this->components);
        }

        if ($this->content) {
            $service->body($this->content);
        }

        if ($type === WhatsAppType::REACTION) {
            $service->messageId($this->messageId ?? '')->emoji($this->emoji ?? '');
        }

        if ($this->interactive) {
            $service->interactive($this->interactive);

            return $service->send();
        }

        if ($this->location) {
            $service->location($this->location['latitude'], $this->location['longitude'], $this->location['name'] ?? null, $this->location['address'] ?? null);

            return $service->send();
        }

        return $service->send();
    }
}
