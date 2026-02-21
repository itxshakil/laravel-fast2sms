<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Illuminate\Support\Collection;
use Shakil\Fast2sms\DataTransferObjects\WhatsAppParameters;
use Shakil\Fast2sms\Enums\WhatsAppType;
use Shakil\Fast2sms\Jobs\SendWhatsAppJob;

/**
 * Trait to manage WhatsApp parameters and queueing for Fast2sms.
 */
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
     * Get the recipient mobile number.
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * Get the sender phone number ID.
     */
    public function getFromPhoneNumberId(): ?string
    {
        return $this->fromPhoneNumberId;
    }

    /**
     * Get the message type.
     */
    public function getType(): ?WhatsAppType
    {
        return $this->type;
    }

    /**
     * Get the message body text.
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Get the template ID.
     */
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

    /**
     * Get the media URL.
     */
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    /**
     * Get the document filename.
     */
    public function getDocumentFilename(): ?string
    {
        return $this->documentFilename;
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

    /**
     * Queue the WhatsApp message.
     */
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

    /**
     * Reset the fluent state for the next request.
     */
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
        $this->components = null;

        $this->resetQueueConfig();
    }
}
