<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use DateTimeInterface;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;

/**
 * Trait to manage SMS parameters for Fast2sms.
 */
trait ManagesSmsParameters
{
    /**
     * The recipient mobile numbers.
     *
     * @var array
     */
    protected array $numbers = [];

    /**
     * The SMS message content or DLT message ID.
     *
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * The DLT approved sender ID.
     *
     * @var string|null
     */
    protected ?string $senderId = null;

    /**
     * The SMS route to use (e.g., 'dlt', 'otp', 'q').
     *
     * @var SmsRoute
     */
    protected SmsRoute $route;

    /**
     * The DLT Principal Entity ID.
     *
     * @var string|null
     */
    protected ?string $entityId = null;

    /**
     * The DLT Content Template ID.
     *
     * @var string|null
     */
    protected ?string $templateId = null;

    /**
     * Variables values for DLT templates, pipe-separated.
     *
     * @var string|null
     */
    protected ?string $variablesValues = null;

    /**
     * Whether to send a flash message.
     *
     * @var bool
     */
    protected bool $flash = false;

    /**
     * The scheduled time for the SMS (YYYY-MM-DD-HH-MM).
     *
     * @var string|null
     */
    protected ?string $scheduleTime = null;

    /**
     * The language of the SMS message.
     *
     * @var SmsLanguage
     */
    protected SmsLanguage $language;

    /**
     * Set the recipient mobile number(s).
     *
     * @param string|array $numbers Single number as string or multiple numbers as an array.
     * @return ManagesSmsParameters|Fast2sms
     */
    public function to(string|array $numbers): self
    {
        $this->numbers = is_array($numbers) ? $numbers : [$numbers];
        return $this;
    }

    /**
     * Set the SMS message content.
     * For DLT routes, this should be the DLT approved Message ID.
     * For Quick SMS, this is the actual message text.
     *
     * @param string $message The SMS message content or DLT message ID.
     * @return ManagesSmsParameters|Fast2sms
     */
    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the DLT approved sender ID.
     *
     * @param string $senderId The DLT approved sender ID (3-6 letters).
     * @return ManagesSmsParameters|Fast2sms
     */
    public function senderId(string $senderId): self
    {
        $this->senderId = $senderId;
        return $this;
    }

    /**
     * Set the SMS route.
     *
     * @param SmsRoute $route The SMS route enum (e.g., SmsRoute::DLT, SmsRoute::OTP, SmsRoute::QUICK).
     * @return ManagesSmsParameters|Fast2sms
     */
    public function route(SmsRoute $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Set the DLT Principal Entity ID.
     * Required for DLT routes.
     *
     * @param string $entityId The DLT Principal Entity ID.
     * @return ManagesSmsParameters|Fast2sms
     */
    public function entityId(string $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * Set the DLT Content Template ID.
     * Required for DLT routes.
     *
     * @param string $templateId The DLT Content Template ID.
     * @return ManagesSmsParameters|Fast2sms
     */
    public function templateId(string $templateId): self
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * Set the variable values for DLT templates.
     * Values should be provided as an array and will be pipe-separated.
     *
     * @param array|string $values An array of variable values.
     * @return ManagesSmsParameters|Fast2sms
     */
    public function variables(array|string $values): self
    {
        $values = is_array($values) ? $values : [$values];
        $this->variablesValues = implode('|', $values);
        return $this;
    }

    /**
     * Set whether to send a flash message.
     *
     * @param bool $flash True to send as flash message, false otherwise.
     * @return ManagesSmsParameters|Fast2sms
     */
    public function flash(bool $flash = true): self
    {
        $this->flash = $flash;
        return $this;
    }

    /**
     * Schedule the SMS to be sent at a future time.
     *
     * @param DateTimeInterface|string $time The schedule time (DateTimeInterface object or YYYY-MM-DD-HH-MM string).
     * @return ManagesSmsParameters|Fast2sms
     *
     * @throws Fast2smsException If the time format is invalid.
     */
    public function schedule(string|DateTimeInterface $time): self
    {
        if ($time instanceof DateTimeInterface) {
            $this->scheduleTime = $time->format('Y-m-d-H-i');
        } elseif (is_string($time)) {
            // Validate string format YYYY-MM-DD-HH-MM
            if (!preg_match('/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}$/', $time)) {
                throw new Fast2smsException("Invalid schedule time format. Expected YYYY-MM-DD-HH-MM.");
            }
            $this->scheduleTime = $time;
        } else {
            throw new Fast2smsException("Invalid schedule time provided. Must be a DateTimeInterface or YYYY-MM-DD-HH-MM string.");
        }
        return $this;
    }

    /**
     * Set the language of the SMS message.
     *
     * @param SmsLanguage $language The SMS language enum (e.g., SmsLanguage::ENGLISH, SmsLanguage::UNICODE).
     * @return ManagesSmsParameters|Fast2sms
     */
    public function language(SmsLanguage $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Get the current numbers.
     *
     * @return array
     */
    public function getNumbers(): array
    {
        return $this->numbers;
    }

    /**
     * Get the current message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the current route.
     *
     * @return SmsRoute
     */
    public function getRoute(): SmsRoute
    {
        return $this->route;
    }

    /**
     * Get the current language.
     *
     * @return SmsLanguage
     */
    public function getLanguage(): SmsLanguage
    {
        return $this->language;
    }

    /**
     * Get the current sender ID.
     *
     * @return string|null
     */
    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    /**
     * Get the current entity ID.
     *
     * @return string|null
     */
    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * Get the current template ID.
     *
     * @return string|null
     */
    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    /**
     * Get the current variables values.
     *
     * @return array|string|null
     */
    public function getVariablesValues(): array|string|null
    {
        return $this->variablesValues;
    }

    /**
     * Get the current flash status.
     *
     * @return bool
     */
    public function isFlash(): bool
    {
        return $this->flash;
    }

    /**
     * Get the current schedule time.
     *
     * @return string|null
     */
    public function getScheduleTime(): ?string
    {
        return $this->scheduleTime;
    }

    /**
     * Reset the state of the Fast2sms instance after sending.
     * This ensures that subsequent calls start with a clean slate.
     *
     * @return void
     */
    protected function resetParameters(): void
    {
        $this->numbers = [];
        $this->message = null;
        $this->senderId = config('fast2sms.default_sender_id'); // Reset to default
        $this->route = SmsRoute::from(config('fast2sms.default_route')); // Reset to default
        $this->entityId = null;
        $this->templateId = null;
        $this->variablesValues = null;
        $this->flash = false;
        $this->scheduleTime = null;
        $this->language = SmsLanguage::ENGLISH;
    }
}
