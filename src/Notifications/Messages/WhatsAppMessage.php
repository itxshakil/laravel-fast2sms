<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Notifications\Messages;

use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Facades\Fast2sms;
use Shakil\Fast2sms\Responses\WhatsAppResponse;

/**
 * WhatsApp Message builder for Fast2SMS notifications.
 */
class WhatsAppMessage
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
     * Set the message content (body).
     */
    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the recipient number.
     */
    public function to(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Set the WhatsApp message type.
     */
    public function type(WhatsAppType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the template and its variables.
     *
     * @param array<int, string>|array<string, mixed> $variables
     */
    public function template(string|int $templateId, array $variables = []): self
    {
        $this->templateId = $templateId;
        $this->variables = $variables;

        return $this;
    }

    /**
     * Set the media URL.
     */
    public function media(string $url): self
    {
        $this->mediaUrl = $url;

        return $this;
    }

    /**
     * Set the document filename.
     */
    public function documentFilename(string $filename): self
    {
        $this->documentFilename = $filename;

        return $this;
    }

    /**
     * Set Meta format components.
     *
     * @param array<int, array<string, mixed>> $components
     */
    public function components(array $components): self
    {
        $this->components = $components;

        return $this;
    }

    /**
     * Set interactive message payload.
     *
     * @param array<string, mixed> $interactive
     */
    public function interactive(array $interactive): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    /**
     * Set location payload.
     *
     * @param array<string, mixed> $location
     */
    public function location(array $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Send the message immediately.
     */
    public function send(): WhatsAppResponse
    {
        if (! $this->to) {
            throw new Fast2smsException('Recipient number is required for WhatsApp message.');
        }

        $service = Fast2sms::viaWhatsApp($this->to);

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

        if ($this->interactive) {
            return $service->sendInteractive($this->interactive);
        }

        if ($this->location) {
            return $service->sendLocation($this->location['latitude'], $this->location['longitude'], $this->location['name'] ?? null, $this->location['address'] ?? null);
        }

        return $service->send();
    }
}
