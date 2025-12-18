<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Jobs\SendSmsJob;

/**
 * Trait QueuesSms.
 *
 * Provides queueing functionality for SMS messages in the Fast2sms package.
 * This trait handles the configuration and execution of queued SMS jobs,
 * allowing for delayed sending and custom queue configurations.
 *
 * Features:
 * - Queue configuration (connection, name, delay)
 * - Support for Quick SMS, DLT SMS, and OTP SMS queueing
 * - Automatic queue configuration reset after job dispatch
 *
 *
 * @property-read ?string $queueConnection Connection name for the queue
 * @property-read ?string $queueName Queue name
 * @property-read ?int $queueDelay Delay in seconds before processing the job
 */
trait QueuesSms
{
    protected ?string $queueConnection = null;

    protected ?string $queueName = null;

    protected ?int $queueDelay = null;

    /**
     * Queue a quick SMS with minimal configuration.
     *
     * @param string|array     $numbers  One or more recipient numbers.
     * @param string           $message  The SMS message content.
     * @param SmsLanguage|null $language Optional message language.
     *
     * @throws Fast2smsException If validation fails.
     */
    public function quickQueue(string|array $numbers, string $message, ?SmsLanguage $language = null): void
    {
        $this->setQuick($numbers, $message, $language);

        $this->queue();
    }

    /**
     * Queue the SMS for sending.
     *
     * @throws Fast2smsException
     */
    public function queue(): void
    {
        $this->validateForRoute();

        $parameters = SmsParameters::fromFast2sms($this);

        $job = new SendSmsJob($parameters);

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

        $this->resetQueueConfig();
    }

    /**
     * Set the queue connection to be used.
     *
     * @return QueuesSms|Fast2sms
     */
    public function onConnection(string $connection): self
    {
        $this->queueConnection = $connection;

        return $this;
    }

    /**
     * Set the queue name to be used.
     *
     * @return QueuesSms|Fast2sms
     */
    public function onQueue(string $queue): self
    {
        $this->queueName = $queue;

        return $this;
    }

    /**
     * Set the delay for the queued job.
     *
     * @return QueuesSms|Fast2sms
     */
    public function delay(int $seconds): self
    {
        $this->queueDelay = $seconds;

        return $this;
    }

    /**
     * Queue an SMS via DLT route.
     *
     * @param string|array $numbers         One or more recipient numbers.
     * @param string       $templateId      The registered DLT template ID.
     * @param array|string $variablesValues Template variable values.
     * @param string|null  $senderId        Optional sender ID.
     * @param string|null  $entityId        Optional entity ID (required for DLT_MANUAL route).
     *
     * @throws Fast2smsException If validation fails.
     */
    public function dltQueue(
        string|array $numbers,
        string $templateId,
        array|string $variablesValues,
        ?string $senderId = null,
        ?string $entityId = null,
    ): void {
        $this->setDlt($numbers, $templateId, $variablesValues, $senderId, $entityId);

        $this->queue();
    }

    /**
     * Queue an OTP SMS.
     *
     * @param string|array $numbers  One or more recipient numbers.
     * @param string       $otpValue The OTP code to send.
     *
     * @throws Fast2smsException If validation fails.
     */
    public function otpQueue(string|array $numbers, string $otpValue): void
    {
        $this->setOtp($numbers, $otpValue);
        $this->queue();
    }

    /**
     * Reset queue configuration.
     */
    private function resetQueueConfig(): void
    {
        $this->queueConnection = null;
        $this->queueName = null;
        $this->queueDelay = null;
    }
}
