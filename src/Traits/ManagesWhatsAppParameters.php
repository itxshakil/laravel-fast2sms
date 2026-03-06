<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Illuminate\Support\Collection;

use function is_array;

use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Jobs\SendWhatsAppJob;

trait ManagesWhatsAppParameters
{
    use HasQueueing;

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

    protected ?string $messageId = null;

    protected ?string $emoji = null;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $location = null;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $interactive = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $components = null;

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

    public function from(string $phoneNumberId): self
    {
        $this->fromPhoneNumberId = $phoneNumberId;

        return $this;
    }

    public function type(WhatsAppType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function body(string $text): self
    {
        $this->body = $text;

        return $this;
    }

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

    public function media(string $url): self
    {
        $this->mediaUrl = $url;

        return $this;
    }

    public function documentFilename(string $filename): self
    {
        $this->documentFilename = $filename;

        return $this;
    }

    public function messageId(string $id): self
    {
        $this->messageId = $id;

        return $this;
    }

    public function emoji(string $emoji): self
    {
        $this->emoji = $emoji;

        return $this;
    }

    /**
     * Set the location for location messages.
     */
    public function location(float $latitude, float $longitude, ?string $name = null, ?string $address = null): self
    {
        $this->location = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'name' => $name,
            'address' => $address,
        ];

        return $this;
    }

    /**
     * Set the interactive payload for interactive messages.
     *
     * @param array<string, mixed> $interactive
     */
    public function interactive(array $interactive): self
    {
        $this->interactive = $interactive;

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

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function getFromPhoneNumberId(): ?string
    {
        return $this->fromPhoneNumberId;
    }

    public function getType(): ?WhatsAppType
    {
        return $this->type;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    /**
     * Get the variables for template messages.
     *
     * @return array<int, string>|array<string, mixed>|null
     */
    public function getVariables(): ?array
    {
        return $this->variables;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function getDocumentFilename(): ?string
    {
        return $this->documentFilename;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getInteractive(): ?array
    {
        return $this->interactive;
    }

    /**
     * Get the components for template messages.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function getComponents(): ?array
    {
        return $this->components;
    }

    public function queue(): void
    {
        $parameters = WhatsAppParameters::fromWhatsApp($this);

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

    protected function resetWhatsAppParameters(): void
    {
        $this->to = null;
        $this->fromPhoneNumberId = null;
        $this->type = null;
        $this->body = null;
        $this->templateId = null;
        $this->variables = null;
        $this->mediaUrl = null;
        $this->documentFilename = null;
        $this->messageId = null;
        $this->emoji = null;
        $this->location = null;
        $this->interactive = null;
        $this->components = null;

        $this->resetQueueConfig();
    }
}
