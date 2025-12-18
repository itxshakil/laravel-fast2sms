<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use DateTimeInterface;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Responses\Fast2smsResponse;

/**
 * Defines the contract for the Fast2sms service.
 */
interface Fast2smsInterface
{
    /**
     * Set the recipient mobile number(s).
     *
     * @param  string|array $numbers Single number as string or multiple numbers as an array.
     * @return $this
     */
    public function to(string|array $numbers): self;

    /**
     * Set the SMS message content.
     * For DLT routes, this should be the DLT approved Message ID.
     * For Quick SMS, this is the actual message text.
     *
     * @param  string $message The SMS message content or DLT message ID.
     * @return $this
     */
    public function message(string $message): self;

    /**
     * Set the DLT approved sender ID.
     *
     * @param  string $senderId The DLT approved sender ID (3-6 letters).
     * @return $this
     */
    public function senderId(string $senderId): self;

    /**
     * Set the SMS route.
     *
     * @param  SmsRoute $route The SMS route enum (e.g., SmsRoute::DLT, SmsRoute::OTP, SmsRoute::QUICK).
     * @return $this
     */
    public function route(SmsRoute $route): self;

    /**
     * Set the DLT Principal Entity ID.
     * Required for DLT routes.
     *
     * @param  string $entityId The DLT Principal Entity ID.
     * @return $this
     */
    public function entityId(string $entityId): self;

    /**
     * Set the DLT Content Template ID.
     * Required for DLT routes.
     *
     * @param  string $templateId The DLT Content Template ID.
     * @return $this
     */
    public function templateId(string $templateId): self;

    /**
     * Set the variable values for DLT templates.
     * Values should be provided as an array and will be pipe-separated.
     *
     * @param  array $values An array of variable values.
     * @return $this
     */
    public function variables(array $values): self;

    /**
     * Set whether to send a flash message.
     *
     * @param  bool  $flash True to send as flash message, false otherwise.
     * @return $this
     */
    public function flash(bool $flash = true): self;

    /**
     * Schedule the SMS to be sent at a future time.
     *
     * @param  DateTimeInterface|string $time The schedule time (DateTimeInterface object or YYYY-MM-DD-HH-MM string).
     * @return $this
     *
     * @throws Fast2smsException If the time format is invalid.
     */
    public function schedule(string|DateTimeInterface $time): self;

    /**
     * Set the language of the SMS message.
     *
     * @param  SmsLanguage $language The SMS language enum (e.g., SmsLanguage::ENGLISH, SmsLanguage::UNICODE).
     * @return $this
     */
    public function language(SmsLanguage $language): self;

    /**
     * Send the SMS message.
     *
     * @return Fast2smsResponse The API response.
     *
     * @throws Fast2smsException If required parameters are missing or API call fails.
     */
    public function send(): Fast2smsResponse;
}
