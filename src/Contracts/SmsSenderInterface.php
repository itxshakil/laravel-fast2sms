<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;

/**
 * Interface for the SMS sender service.
 */
interface SmsSenderInterface
{
    /**
     * Send a quick SMS.
     *
     * @param string|array<int, string> $numbers
     */
    public function quick(string|array $numbers, string $message, ?SmsLanguage $language = null): ResponseInterface;

    /**
     * Send a DLT SMS.
     *
     * @param string|array<int, string>       $numbers
     * @param array<int|string, mixed>|string $variablesValues
     */
    public function dlt(string|array $numbers, string $templateId, array|string $variablesValues, ?string $senderId = null, ?string $entityId = null): ResponseInterface;

    /**
     * Send an OTP SMS.
     *
     * @param string|array<int, string> $numbers
     */
    public function otp(string|array $numbers, string $otpValue): ResponseInterface;

    /**
     * Set the recipient mobile number(s).
     *
     * @param  string|array<int, string|int>|Collection<int, string|int> $numbers Single number as string or multiple numbers as an array.
     * @return $this
     */
    public function to(string|array|Collection $numbers): self;

    /**
     * Set the SMS message content.
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
     * @param  array<int, string> $values An array of variable values.
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
     * @return ResponseInterface The API response.
     *
     * @throws Fast2smsException If required parameters are missing or API call fails.
     */
    public function send(): ResponseInterface;

    /**
     * Configure fluent state for a quick SMS (without sending).
     *
     * @param array<int, string|int>|string $numbers
     */
    public function setQuick(array|string $numbers, string $message, ?SmsLanguage $language): void;

    /**
     * Configure fluent state for a DLT SMS (without sending).
     *
     * @param array<int, string>|string $numbers
     * @param array<int, string>|string $variablesValues
     */
    public function setDlt(array|string $numbers, string $templateId, array|string $variablesValues, ?string $senderId, ?string $entityId): void;

    /**
     * Configure fluent state for an OTP SMS (without sending).
     *
     * @param array<int, string|int>|string $numbers
     */
    public function setOtp(array|string $numbers, string $otpValue): void;

    /**
     * Queue a quick SMS message.
     *
     * @param string|array<int, string|int> $numbers
     */
    public function quickQueue(string|array $numbers, string $message, ?SmsLanguage $language = null): void;

    /**
     * Queue a DLT SMS message.
     *
     * @param string|array<int, string> $numbers
     * @param array<int, string>|string $variablesValues
     */
    public function dltQueue(string|array $numbers, string $templateId, array|string $variablesValues, ?string $senderId = null, ?string $entityId = null): void;

    /**
     * Queue an OTP SMS message.
     *
     * @param string|array<int, string|int> $numbers
     */
    public function otpQueue(string|array $numbers, string $otpValue): void;

    /** @return array<int, string|int> */
    public function getNumbers(): array;

    public function getMessage(): ?string;

    public function getRoute(): SmsRoute;

    public function getLanguage(): SmsLanguage;

    public function getSenderId(): ?string;

    public function getEntityId(): ?string;

    public function getTemplateId(): ?string;

    /** @return array<int, string>|string|null */
    public function getVariablesValues(): array|string|null;

    public function isFlash(): bool;

    public function getScheduleTime(): ?string;

    /**
     * Queue the SMS message.
     */
    public function queue(): void;

    /**
     * Set the queue connection.
     */
    public function onConnection(string $connection): self;

    /**
     * Set the queue name.
     */
    public function onQueue(string $queue): self;

    /**
     * Set the queue delay.
     */
    public function delay(int $seconds): self;
}
