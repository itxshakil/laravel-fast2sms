<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Exceptions\DuplicateSendException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\InsufficientBalanceException;
use Shakil\Fast2sms\Exceptions\ThrottleExceededException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\Jobs\SendSmsJob;
use Throwable;

trait ManagesSms
{
    use AppliesSendGuards;
    use HasQueueing;
    use ManagesSmsValidation;

    /**
     * The recipient mobile numbers.
     *
     * @var array<int, string|int>
     */
    protected array $numbers = [];

    /**
     * The SMS message content or DLT message ID.
     */
    protected ?string $message = null;

    /**
     * The DLT approved sender ID.
     */
    protected ?string $senderId = null;

    /**
     * The SMS route to use.
     */
    protected SmsRoute $route;

    /**
     * The DLT Principal Entity ID.
     */
    protected ?string $entityId = null;

    /**
     * The DLT Content Template ID.
     */
    protected ?string $templateId = null;

    /**
     * Variables values for DLT templates, pipe-separated.
     */
    protected ?string $variablesValues = null;

    /**
     * Whether to send a flash message.
     */
    protected bool $flash = false;

    /**
     * The scheduled time for the SMS (YYYY-MM-DD-HH-MM).
     */
    protected ?string $scheduleTime = null;

    /**
     * The language of the SMS message.
     */
    protected SmsLanguage $language;

    /**
     * Quickly send an SMS with minimal configuration.
     *
     * @throws Fast2smsException
     */
    public function quick(string|array $numbers, string $message, ?SmsLanguage $language = null): ResponseInterface
    {
        $this->ensureSmsInitialized();
        $this->setQuick($numbers, $message, $language);

        return $this->send();
    }

    /**
     * Send an SMS via DLT route.
     *
     * @throws Fast2smsException
     */
    public function dlt(string|array $numbers, string $templateId, array|string $variablesValues, ?string $senderId = null, ?string $entityId = null): ResponseInterface
    {
        $this->ensureSmsInitialized();
        $this->setDlt($numbers, $templateId, $variablesValues, $senderId, $entityId);

        return $this->send();
    }

    /**
     * Send an OTP SMS.
     *
     * @throws Fast2smsException
     */
    public function otp(string|array $numbers, string $otpValue): ResponseInterface
    {
        $this->ensureSmsInitialized();
        $this->setOtp($numbers, $otpValue);

        return $this->send();
    }

    /**
     * Queue a quick SMS.
     *
     * @param array<int, string|int>|string $numbers
     *
     * @throws Fast2smsException
     */
    public function quickQueue(string|array $numbers, string $message, ?SmsLanguage $language = null): void
    {
        $this->ensureSmsInitialized();
        $this->setQuick($numbers, $message, $language);

        $this->queue();
    }

    /**
     * Queue an SMS via DLT route.
     *
     * @param array<int, string|int>|string $numbers
     * @param array<int, string>|string     $variablesValues
     *
     * @throws Fast2smsException
     */
    public function dltQueue(string|array $numbers, string $templateId, array|string $variablesValues, ?string $senderId = null, ?string $entityId = null): void
    {
        $this->ensureSmsInitialized();
        $this->setDlt($numbers, $templateId, $variablesValues, $senderId, $entityId);

        $this->queue();
    }

    /**
     * Queue an OTP SMS.
     *
     * @param array<int, string|int>|string $numbers
     *
     * @throws Fast2smsException
     */
    public function otpQueue(string|array $numbers, string $otpValue): void
    {
        $this->ensureSmsInitialized();
        $this->setOtp($numbers, $otpValue);

        $this->queue();
    }

    /**
     * Queue the SMS for sending.
     *
     * @throws Fast2smsException
     */
    public function queue(): void
    {
        $this->ensureSmsInitialized();

        $this->validateForRoute();

        $parameters = SmsParameters::fromFast2sms($this);

        $job = new SendSmsJob($parameters);

        $this->queueConnection && $job->onConnection($this->queueConnection);
        $this->queueName && $job->onQueue($this->queueName);
        $this->queueDelay && $job->delay($this->queueDelay);

        dispatch($job);

        $this->resetQueueConfig();
        $this->resetParameters();
    }

    /**
     * Set the recipient mobile number(s).
     *
     * @param string|array<int, string|int>|Collection<int, string|int> $numbers
     */
    public function to(string|array|Collection $numbers): self
    {
        $this->ensureSmsInitialized();

        $this->numbers = $numbers instanceof Collection
            ? $numbers->all()
            : (array) $numbers;

        return $this;
    }

    public function message(string $message): self
    {
        $this->ensureSmsInitialized();

        $this->message = $message;

        return $this;
    }

    public function senderId(string $senderId): self
    {
        $this->ensureSmsInitialized();

        $this->senderId = $senderId;

        return $this;
    }

    public function route(SmsRoute $route): self
    {
        $this->ensureSmsInitialized();

        $this->route = $route;

        return $this;
    }

    public function entityId(string $entityId): self
    {
        $this->ensureSmsInitialized();

        $this->entityId = $entityId;

        return $this;
    }

    public function templateId(string $templateId): self
    {
        $this->ensureSmsInitialized();

        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Set the variable values for DLT templates.
     */
    public function variables(array|string $values): self
    {
        $this->ensureSmsInitialized();

        $this->variablesValues = implode('|', (array) $values);

        return $this;
    }

    public function flash(bool $flash = true): self
    {
        $this->ensureSmsInitialized();

        $this->flash = $flash;

        return $this;
    }

    /**
     * Schedule the SMS to be sent at a future time.
     */
    public function schedule(string|DateTimeInterface $time): self
    {
        $this->ensureSmsInitialized();

        if ($time instanceof DateTimeInterface) {
            $time = $time->format('Y-m-d-H-i');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}$/', $time)) {
            throw new Fast2smsException('Invalid schedule time format. Expected YYYY-MM-DD-HH-MM.');
        }

        $this->scheduleTime = $time;

        return $this;
    }

    public function language(SmsLanguage $language): self
    {
        $this->ensureSmsInitialized();

        $this->language = $language;

        return $this;
    }

    /**
     * @param array<int, string|int>|string $numbers
     */
    public function setQuick(array|string $numbers, string $message, ?SmsLanguage $language): void
    {
        $this->to($numbers)->message($message)->route(SmsRoute::QUICK);
        $language && $this->language($language);
    }

    /**
     * @param array<int, string|int>|string $numbers
     * @param array<int, string>|string     $variablesValues
     */
    public function setDlt(array|string $numbers, string $templateId, array|string $variablesValues, ?string $senderId, ?string $entityId): void
    {
        $this->to($numbers)
            ->templateId($templateId)
            ->variables($variablesValues)
            ->route(SmsRoute::DLT);

        $senderId && $this->senderId($senderId);
        $entityId && $this->entityId($entityId);
    }

    /**
     * @param array<int, string|int>|string $numbers
     */
    public function setOtp(array|string $numbers, string $otpValue): void
    {
        $this->to($numbers)->message($otpValue)->route(SmsRoute::OTP);
    }

    /** @return array<int, string|int> */
    public function getNumbers(): array
    {
        return $this->numbers;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getRoute(): SmsRoute
    {
        return $this->route;
    }

    public function getLanguage(): SmsLanguage
    {
        return $this->language;
    }

    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    /** @return array<int, string>|string|null */
    public function getVariablesValues(): array|string|null
    {
        return $this->variablesValues;
    }

    public function isFlash(): bool
    {
        return $this->flash;
    }

    /**
     * Get the current schedule time.
     */
    public function getScheduleTime(): ?string
    {
        return $this->scheduleTime;
    }

    /**
     * Initialize SMS default parameters from configuration.
     */
    protected function initializeSmsDefaults(): void
    {
        $this->senderId = $this->config->defaultSenderId;
        $this->route = $this->config->defaultRoute;
        $this->language = SmsLanguage::ENGLISH;
    }

    /**
     * Execute an SMS send request, applying all cost-saving guards.
     *
     * @throws Fast2smsException
     * @throws Throwable
     * @throws InvalidArgumentException
     * @throws DuplicateSendException
     * @throws InsufficientBalanceException
     * @throws ThrottleExceededException
     * @throws ValidationException
     */
    protected function executeSend(): ResponseInterface
    {
        $this->ensureSmsInitialized();
        $this->validateForRoute();

        if (config('fast2sms.recipients.deduplicate', true)) {
            $this->numbers = $this->deduplicateRecipients($this->numbers);
        }

        if (config('fast2sms.validation.strip_invalid_recipients', false)) {
            $this->numbers = $this->filterValidRecipients($this->numbers);
        }

        $commitDedup = $this->applySendGuards(implode(',', $this->numbers) . '|' . $this->message . '|' . $this->route->value);

        $chunks = $this->chunkRecipients($this->numbers);
        $response = null;
        $payload = [];

        try {
            foreach ($chunks as $chunk) {
                $this->numbers = $chunk;
                $payload = $this->buildPayloadForRoute();
                $response = $this->executeApiCall($payload);
            }
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new SmsFailed($payload, $e));
            }
            throw $e;
        }

        if ($response === null) {
            throw new Fast2smsException('No API response was received — the recipient list may have been empty after chunking.');
        }

        $commitDedup();

        return $response;
    }

    /**
     * Ensure SMS defaults are initialized.
     */
    protected function ensureSmsInitialized(): void
    {
        if (! isset($this->route)) {
            $this->initializeSmsDefaults();
        }
    }

    /**
     * Reset the state of the Fast2sms instance after sending.
     */
    protected function resetParameters(): void
    {
        $this->numbers = [];
        $this->message = null;
        $this->senderId = $this->config->defaultSenderId;
        $this->route = $this->config->defaultRoute;
        $this->entityId = null;
        $this->templateId = null;
        $this->variablesValues = null;
        $this->flash = false;
        $this->scheduleTime = null;
        $this->language = SmsLanguage::ENGLISH;

        $this->resetQueueConfig();
    }
}
