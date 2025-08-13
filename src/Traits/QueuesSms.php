<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Fast2sms;
use Shakil\Fast2sms\Jobs\SendSmsJob;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;

trait QueuesSms
{
    protected ?string $queueConnection = null;
    protected ?string $queueName = null;
    protected ?int $queueDelay = null;

    /**
     * Set the queue connection to be used.
     *
     * @param string $connection
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
     * @param string $queue
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
     * @param int $seconds
     * @return QueuesSms|Fast2sms
     */
    public function delay(int $seconds): self
    {
        $this->queueDelay = $seconds;
        return $this;
    }

    /**
     * Queue the SMS for sending.
     *
     * @return void
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
     * Queue a quick SMS with minimal configuration.
     *
     * @param string|array $numbers One or more recipient numbers.
     * @param string $message The SMS message content.
     * @param SmsLanguage|null $language Optional message language.
     *
     * @return void
     *
     * @throws Fast2smsException If validation fails.
     */
    public function quickQueue(string|array $numbers, string $message, ?SmsLanguage $language = null): void
    {
        $this->to($numbers)->message($message)->route(SmsRoute::QUICK);
        if ($language) {
            $this->language($language);
        }
        $this->queue();
    }

    /**
     * Queue an SMS via DLT route.
     *
     * @param string|array $numbers One or more recipient numbers.
     * @param string $templateId The registered DLT template ID.
     * @param array|string $variablesValues Template variable values.
     * @param string|null $senderId Optional sender ID.
     * @param string|null $entityId Optional entity ID (required for DLT_MANUAL route).
     *
     * @return void
     *
     * @throws Fast2smsException If validation fails.
     */
    public function dltQueue(
        string|array $numbers,
        string $templateId,
        array|string $variablesValues,
        ?string $senderId = null,
        ?string $entityId = null
    ): void {
        $this->to($numbers)
            ->message($templateId)
            ->templateId($templateId)
            ->variables($variablesValues)
            ->route(SmsRoute::DLT);

        if ($senderId) {
            $this->senderId($senderId);
        }
        if ($entityId) {
            $this->entityId($entityId);
        }

        $this->queue();
    }

    /**
     * Queue an OTP SMS.
     *
     * @param string|array $numbers One or more recipient numbers.
     * @param string $otpValue The OTP code to send.
     *
     * @return void
     *
     * @throws Fast2smsException If validation fails.
     */
    public function otpQueue(string|array $numbers, string $otpValue): void
    {
        $this->to($numbers)->message($otpValue)->route(SmsRoute::OTP);
        $this->queue();
    }

    /**
     * Reset queue configuration.
     *
     * @return void
     */
    private function resetQueueConfig(): void
    {
        $this->queueConnection = null;
        $this->queueName = null;
        $this->queueDelay = null;
    }
}
