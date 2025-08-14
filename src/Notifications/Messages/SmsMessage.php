<?php
declare(strict_types=1);

namespace Shakil\Fast2sms\Notifications\Messages;

use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;

/**
 * SMS Message builder for Fast2SMS notifications.
 *
 * This class provides a fluent interface for building SMS messages with
 * various Fast2SMS specific features like DLT templates, sender IDs,
 * and language settings.
 *
 * @package Shakil\Fast2sms\Notifications\Messages
 *
 * @property-read string|null $content The message content
 * @property-read string|null $templateId The DLT template ID
 * @property-read array|null $variables Template variables
 * @property-read string|null $senderId Sender ID for the message
 * @property-read SmsRoute|null $route SMS route (QUICK/DLT/OTP)
 * @property-read SmsLanguage|null $language Message language
 */
class SmsMessage
{
    /**
     * @param string $content
     */
    public function __construct(string $content = ''){
        if ($content) {
            $this->content($content);
        }
    }
    /**
     * The message content.
     *
     * @var string|null
     */
    protected ?string $content = null;

    /**
     * The DLT template ID.
     *
     * @var string|null
     */
    protected ?string $templateId = null;

    /**
     * The template variables.
     *
     * @var array|null
     */
    protected ?array $variables = null;

    /**
     * The sender ID.
     *
     * @var string|null
     */
    protected ?string $senderId = null;

    /**
     * The SMS route.
     *
     * @var SmsRoute|null
     */
    protected ?SmsRoute $route = null;

    /**
     * The message language.
     *
     * @var SmsLanguage|null
     */
    protected ?SmsLanguage $language = null;

    /**
     * Set the message content.
     *
     * @param string $content The message text
     * @return $this
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set the DLT template and its variables.
     *
     * @param string $templateId The DLT template ID
     * @param array $variables Variables to be replaced in the template
     * @return $this
     */
    public function template(string $templateId, array $variables = []): self
    {
        $this->templateId = $templateId;
        $this->variables = $variables;
        return $this;
    }

    /**
     * Set the sender ID.
     *
     * @param string $senderId The sender ID
     * @return $this
     */
    public function from(string $senderId): self
    {
        $this->senderId = $senderId;
        return $this;
    }

    /**
     * Set the SMS route.
     *
     * @param SmsRoute $route The route to use (QUICK/DLT/OTP)
     * @return $this
     */
    public function route(SmsRoute $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Set the message language.
     *
     * @param SmsLanguage $language The language to use
     * @return $this
     */
    public function language(SmsLanguage $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Get a property value.
     *
     * @param string $name Property name
     * @return mixed The property value
     */
    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
    }
}
